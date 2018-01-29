<?php

namespace SGE\KernelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SGE\EstablecimientoBundle\Services\EstablecimientoManager;
use SGE\EstablecimientoBundle\Services\UsuarioPersonaManager;

/**
 * @Route("/")
 */
class KernelController extends Controller
{

    /**
     * @return UsuarioPersonaManager
     */
    protected function getUsuarioPersonaManager()
    {
        return $this->get(UsuarioPersonaManager::SERVICE_NAME);
    }

    /**
     * @Route("/inicio", name="welcome")
     */
    public function welcomeAction()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $name = $securityContext->getToken()->getUser();
            //return $this->render('SGEKernelBundle::welcome_authenticated.html.twig', array('name' => $name));
            $userId = $securityContext->getToken()->getUser()->getId();
            $userPers = $this->getUsuarioPersonaManager()->getUsuarioPersonaByUsuarioId($userId);
            if(!is_null($userPers)) {
              $this->get('session')->set('personaId', $userPers->getPersona()->getId());
              $this->get('session')->set('personaTipo', $userPers->getTipoPersona());
            } else {
              $this->get('session')->set('personaId', 0);
            }
            $newURL = $this->generateUrl('show_user_establecimiento', array('id' => $userId));
            return $this->redirect($newURL);
        } else {
            return $this->render('SGEKernelBundle::welcome_not_authenticated.html.twig');
        }
    }
}
