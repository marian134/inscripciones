<?php

namespace SGE\KernelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/help")
 */
class HelpFetcherController extends Controller
{

    /**
     * @Route("/fetch", name="sge_fetch_help")
     * @return JsonResponse
     */
    public function fetchHelpAction(Request $request)
    {
            $page = $request->query->get('page');
            $response = new JsonResponse(array('parse' => array('text' =>array("*" =>
                            $this->renderView("SGEKernelBundle:HelpFetcher:$page.html.twig")
                        ))));
            return $response;
    }

    /**
     * @Route("/edit", name="sge_fetch_help_edit")
     */
    public function editHelpAction()
    {
        return $this->render("SGEKernelBundle:HelpFetcher:editHelp.html.twig");
    }

    /**
     * @Route("/home", name="sge_fetch_help_home")
     */
    public function homeHelpAction()
    {
        return $this->render("SGEKernelBundle:HelpFetcher:homeHelp.html.twig");
    }
}
