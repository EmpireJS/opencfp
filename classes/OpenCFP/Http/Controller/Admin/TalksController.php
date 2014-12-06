<?php

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Http\Controller\BaseController;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\View\TwitterBootstrap3View;
use OpenCFP\Http\Controller\FlashableTrait;

class TalksController extends BaseController
{
    use AdminAccessTrait;
    use FlashableTrait;

    private function indexAction(Request $req)
    {
        $admin_user_id = $this->app['sentry']->getUser()->getId();
        $mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $pager_formatted_talks = $mapper->getAllPagerFormatted($admin_user_id);

        // Set up our page stuff
        $adapter = new \Pagerfanta\Adapter\ArrayAdapter($pager_formatted_talks);
        $pagerfanta = new \Pagerfanta\Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->getNbResults();

        if ($req->get('page') !== null) {
            $pagerfanta->setCurrentPage($req->get('page'));
        }

        // Create our default view for the navigation options
        $routeGenerator = function ($page) {
            return '/admin/talks?page=' . $page;
        };
        $view = new TwitterBootstrap3View();
        $pagination = $view->render(
            $pagerfanta,
            $routeGenerator,
            array('proximity' => 3)
        );

        $templateData = array(
            'pagination' => $pagination,
            'talks' => $pagerfanta,
            'page' => $pagerfanta->getCurrentPage(),
            'current_page' => $req->getRequestUri(),
            'totalRecords' => count($pager_formatted_talks)
        );

        return $this->render('admin/talks/index.twig', $templateData);
    }

    public function viewAction(Request $req)
    {
        // Get info about the talks
        $talk_mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $meta_mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\TalkMeta');
        $talk_id = $req->get('id');

        // Mark talk as viewed by admin
        $talk_meta = $meta_mapper->where([
                'admin_user_id' => $this->app['sentry']->getUser()->getId(),
                'talk_id' => (int)$req->get('id'),
            ])
            ->first();

        if (!$talk_meta) {
            $talk_meta = $meta_mapper->get();
        }

        if (!$talk_meta->viewed) {
            $talk_meta->viewed = true;
            $talk_meta->admin_user_id = $this->app['sentry']->getUser()->getId();
            $talk_meta->talk_id = $talk_id;
            $meta_mapper->save($talk_meta);
        }

        $talk = $talk_mapper->where(['id' => $talk_id])
            ->with(['comments'])
            ->first();
        $all_talks = $talk_mapper->all()
            ->where(['user_id' => $talk->user_id])
            ->toArray();

        // Get info about our speaker
        $user_mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\User');
        $speaker = $user_mapper->get($talk->user_id)->toArray();;

        // Grab all the other talks and filter out the one we have
        $otherTalks = array_filter($all_talks, function ($talk) use ($talk_id) {
            if ((int) $talk['id'] == (int) $talk_id) {
                return false;
            }

            return true;
        });

        // Build and render the template
        $templateData = array(
            'talk' => $talk,
            'talk_meta' => $talk_meta,
            'speaker' => $speaker,
            'otherTalks' => $otherTalks,
        );

        return $this->render('admin/talks/view.twig', $templateData);
    }

    private function rateAction(Request $req, Application $app)
    {
        $admin_user_id = (int)$app['sentry']->getUser()->getId();
        $mapper = $app['spot']->mapper('OpenCFP\Domain\Entity\TalkMeta');

        $talk_rating = (int)$req->get('rating');
        var_dump($talk_rating);
        $talk_id = (int)$req->get('id');

        // Check for invalid rating range
        if ($talk_rating < -1 || $talk_rating > 1) {
            return false;
        }

        $talk_meta = $mapper->where([
                'admin_user_id' => $admin_user_id,
                'talk_id' => (int)$req->get('id')
            ])
            ->first();

        if (!$talk_meta) {
            $talk_meta = $mapper->get();
            $talk_meta->admin_user_id = $admin_user_id;
            $talk_meta->talk_id = $talk_id;
        }

        $talk_meta->rating = $talk_rating;
        $mapper->save($talk_meta);

        return true;
    }

    /**
     * Set Favorited Talk [POST]
     *
     * @param Request $req Request Object
     * @return bool
     */
    private function favoriteAction(Request $req)
    {
        $admin_user_id = (int) $this->app['sentry']->getUser()->getId();
        $status = true;

        if ($req->get('delete') !== null) {
            $status = false;
        }

        $mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Favorite');

        if ($status == false) {
            // Delete the record that matches
            $favorite = $mapper->first([
                'admin_user_id' => $admin_user_id,
                'talk_id' => (int) $req->get('id')
            ]);

            return $mapper->delete($favorite);
        }

        $previous_favorite = $mapper->where([
            'admin_user_id' => $admin_user_id,
            'talk_id' => (int) $req->get('id')
        ]);

        if ($previous_favorite->count() == 0) {
            $favorite = $mapper->get();
            $favorite->admin_user_id = $admin_user_id;
            $favorite->talk_id = (int) $req->get('id');

            return $mapper->insert($favorite);
        }

        return true;
    }

    /**
     * Set Selected Talk [POST]
     *
     * @param Request     $req Request Object
     * @return bool
     */
    private function selectAction(Request $req)
    {
        $status = true;

        if ($req->get('delete') !== null) {
            $status = false;
        }

        $mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $talk = $mapper->get($req->get('id'));
        $talk->selected = $status;
        $mapper->save($talk);

        return true;
    }

    private function commentCreateAction(Request $req, Application $app)
    {
        $talk_id = (int)$req->get('id');
        $admin_user_id = (int)$app['sentry']->getUser()->getId();

        $mapper = $app['spot']->mapper('OpenCFP\Entity\TalkComment');
        $comment = $mapper->get();

        $comment->talk_id = $talk_id;
        $comment->user_id = $admin_user_id;
        $comment->message = $req->get('comment');

        $mapper->save($comment);

        $app['session']->set('flash', [
                'type' => 'success',
                'short' => 'Success',
                'ext' => "Comment Added!"
            ]);

        return $app->redirect($app->url('admin_talk_view', ['id' => $talk_id]));
    }
}
