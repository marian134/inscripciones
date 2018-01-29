<?php

namespace SGE\KernelBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use SGE\KernelBundle\Exception\SGEException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Debug\ErrorHandler;

/**
 * Description of SGEController
 *
 * @author pedro
 */
class SGEController extends Controller
{

    const REPORTE_PDF = 'pdf';
    const REPORTE_EXCEL = 'excel';
    const REPORTE_PRINT = 'print';

    public function setExceptionFlashMessage(SGEException $ex, $type = 'danger')
    {
        $this->setFlashMessage($this->translatedException($ex), $type);
    }

    public function setFlashMessage($message, $type)
    {
        $this->get('session')->getFlashBag()->add($type, $message);
    }

    /**
     * @param SGEException $ex
     * @return string
     */
    public function translatedException(SGEException $ex)
    {
        return $ex->getTranslatedMessage($this->getTranslator());
    }

    /**
     * @return Translator
     */
    public function getTranslator()
    {
        return $this->get('translator');
    }

    /**
     * @param $attributes
     * @param $entity
     * @return boolean
     */
    protected function isGranted($attributes, $entity)
    {
        return $this->get('security.context')->isGranted($attributes, $entity);
    }

    /**
     * @param array $options
     * @param array $exportFormats
     * @param string $formName
     */
    private function buildExcelOptions($options, $exportFormats, $formName)
    {
        if (in_array(self::REPORTE_EXCEL, $exportFormats)) {
            $options['generateReport'] = true;
            $options['exportExcel'] = true;
            $options['exportExcelUrl'] = $this->generateUrl($formName.'_reporte_excel');
            $options['exportExcelUrlPrefix'] = $formName.'_reporte/excel';
        } else {
            $options['exportExcel'] = false;
        }

        return $options;
    }

    /**
     * @param array $options
     * @param array $exportFormats
     * @param string $formName
     */
    private function buildPrintOptions($options, $exportFormats, $formName)
    {
        if (in_array(self::REPORTE_PRINT, $exportFormats)) {
            $options['generateReport'] = true;
            $options['printable'] = true;
            $options['printUrl'] = $this->generateUrl($formName.'_reporte_print');
            $options['printUrlPrefix'] = $formName.'_reporte/print';
        } else {
            $options['printable'] = false;
        }

        return $options;
    }

    /**
     * @param array $options
     * @param array $exportFormats
     * @param string $formName
     */
    private function buildPdfOptions($options, $exportFormats, $formName)
    {
        if (in_array(self::REPORTE_PDF, $exportFormats)) {
            $options['generateReport'] = true;
            $options['exportPdf'] = true;
            $options['exportPdfUrl'] = $this->generateUrl($formName.'_reporte_pdf');
            $options['exportPdfUrlPrefix'] = $formName.'_reporte/pdf';
        } else {
            $options['exportPdf'] = false;
        }

        return $options;
    }

    /**
     * @param FormInterface $form
     * @param QueryBuilder $filterBuilder
     * @param array $exportFormats
     * @return array
     */
    protected function buildRenderOptions(Form $form, QueryBuilder $filterBuilder, $exportFormats, $customPagination=null)
    {
    
        $logger = $this->container->get('logger');
     
        $logger->debug('PRE SUBMIT');
           ErrorHandler::register();        
        try{
     
        $form->submit($this->get('request')->get($form->getName()));
        $logger->debug('PRE LEXIK');
        $entities = $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions(
            $form,
            $filterBuilder
        );        
      
        $logger->debug('PRE PAGIN');
        
        if ($customPagination ==null){
            $pagination = $this->get('knp_paginator')->paginate(
                $entities,
                $this->get('request')->query->get('page', 1),
                $this->container->getParameter('offset')
            );
        }else{
              $pagination = $this->get('knp_paginator')->paginate(
                  $entities,
                  $this->get('request')->query->get('page', 1),
                  $customPagination
              );            
        }

        } catch(\Exception $e ) {
          $logger->error('FALLO PAGINACION Message: ' . $e->getMessage() . 'Stack Trace: '. $e->getTraceAsString());
        }
        
        $options = array();
        $options['pagination'] = $pagination;
        $options['isFiltered'] = true;
        $options['generateReport'] = false;
        $formName = $form->getName();
        
        $logger->debug('PRE OPTIONS');

        if(!is_null($exportFormats)) {
          $optionsWithExcel = $this->buildExcelOptions($options, $exportFormats, $formName);
          $optionsWithPrint = $this->buildPrintOptions($optionsWithExcel, $exportFormats, $formName);
          $optionsWithPdf = $this->buildPdfOptions($optionsWithPrint, $exportFormats, $formName);
        } else {
          $optionsWithPdf = $options;
        }

        $logger->debug('POST OPTIONS');
        
        return $optionsWithPdf;
    }
    
    /**
     * @param FormInterface $form
     * @param QueryBuilder $filterBuilder
     * @param array $exportFormats
     * @return array
     */
    protected function buildRenderOptionsNoPagination(Form $form, QueryBuilder $filterBuilder, $exportFormats)
    {
    
        $logger = $this->container->get('logger');
     
        $logger->debug('NO PAG PRE SUBMIT');
        ErrorHandler::register();        
        
        try {
     
          $form->submit($this->get('request')->get($form->getName()));
          $logger->debug('NO PAG PRE LEXIK');
          $entities = $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions(
              $form,
              $filterBuilder
          );
        
          $logger->debug('NO PAG PRE PAGIN');
          
          
          /*$pagination = $this->get('knp_paginator')->paginate(
              $entities,
              $this->get('request')->query->get('page', 1),
              $this->container->getParameter('offset')
          );
          */
          
          $results = $entities->getQuery()->getResult();
        
        } catch(\Exception $e ) {
          $logger->error('FALLO PAGINACION Message: ' . $e->getMessage() . 'Stack Trace: '. $e->getTraceAsString());
        }
        
        $options = array();
        $options['pagination'] = $results;
        $options['isFiltered'] = true;
        $options['generateReport'] = false;
        $formName = $form->getName();
        
        $logger->debug('NO PAG PRE OPTIONS');

        if(!is_null($exportFormats)) {
          $optionsWithExcel = $this->buildExcelOptions($options, $exportFormats, $formName);
          $optionsWithPrint = $this->buildPrintOptions($optionsWithExcel, $exportFormats, $formName);
          $optionsWithPdf = $this->buildPdfOptions($optionsWithPrint, $exportFormats, $formName);
        } else {
          $optionsWithPdf = $options;
        }

        $logger->debug('NO PAG POST OPTIONS');
        
        return $optionsWithPdf;
    }
    
    protected function checkEstablecimientoSeleccionado() {
      $establecimientoId = $this->get('session')->get('idEstablecimiento');
      if(!$establecimientoId) {
        throw new \Exception('Debe elegir un establecimiento de trabajo');
      }
      
      return $establecimientoId;
    }
}
