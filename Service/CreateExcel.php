<?php
namespace Ibtikar\GlanceDashboardBundle\Service;


use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
/**
 * Description of CreateExcel
 *
 * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
 */
class CreateExcel {

    private $container;
    private $dm;
    private $phpExcel;
    private $collection = array();
    private $query;
    private $orignalCollection;
    private $fields = array();
    private $extension;
    private $writerType;
    private $title = "Sheet 1";
    private $excelObj;
    private $excelObjs = array();
    private $subCollection;
    private $translationDomain = "visitor";


    public function __construct($container) {
        $this->container = $container;
        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
        $this->phpExcel = $this->container->get('phpexcel');
        $this->extension = '.xls';
        $this->writerType = 'Excel5';
    }

    function setQuery($query){
        $this->query = $query;
    }

    function setCollection($collection){
        $this->orignalCollection = $collection;
        $this->collection = $collection->toArray();
    }

    function setFields($fields){
        $this->fields = $fields;
    }

    function setTitle($title){
        $this->title = $title;
    }

    function setExtension($extension){
        $this->extension = $extension;
        $this->writerType = $this->extension == '.xls' ? 'Excel5' : 'CSV';
    }

    function setTranslationDomain($translationDomain){
        $this->translationDomain = $translationDomain;
    }

function getFileName(){
    return $this->container->get('translator')->trans('exported excel file', array(), $this->translationDomain);
}

    public function buildMultiple($limit){
        $count = $this->orignalCollection->count();
        if($count <= $limit){
            $this->build();
        }else{
        $filesCount = ceil($count/$limit);

        $col = range('A', 'Z');
        $methods[]=array();
        $this->subCollection = array_chunk($this->collection, $limit);
        $iterableResult = $this->query->iterate();
        $iterableResult->next();

        for($file = 0;$file < $filesCount;$file++){
            $phpExcelObject = $this->phpExcel->createPHPExcelObject();
            $phpExcelObject->getDefaultStyle()->getAlignment()->setWrapText(true);

            $sheet = $phpExcelObject->setActiveSheetIndex(0);

            foreach ($this->fields as $field) {
                    $methods[$field] = "get" . ucfirst($field);
                    $sheet->setCellValue(current($col) . "1", $this->container->get('translator')->trans($field, array(), $this->translationDomain));
                    next($col);
                }
                reset($col);


            for($i = 0;$i < $limit;$i++){
                foreach($this->fields as $field){
    //                    var_dump($field);
                        if ($field != 'group') {
                            if ($field == 'email' && !method_exists(current($this->collection), $methods[$field])) {
                                $value = $iterableResult->current()->getCreatedBy()->$methods[$field]();
                            } elseif ($field == 'married') {
                                $value = $iterableResult->current()->$methods[$field]() == 'Married' ? 'متزوجة' : 'آنسة';
                            } elseif ($field == 'children') {
                                $value = $iterableResult->current()->$methods[$field]() == 'No' ? 'لا' : 'نعم';
                            } elseif ($field == 'employee') {
                                $value = $iterableResult->current()->$methods[$field]() == 'No' ? 'لا' : 'نعم';
                            } elseif ($field == "gender") {
                                $value = $this->container->get('translator')->trans(current($this->collection)->$methods[$field]());
                            } else {
                                $value = $iterableResult->current()->$methods[$field]();
                            }


                            if(is_null($value)){
                            $value = "";
                        }elseif($value instanceof \DateTime){
                            $value = $value->format('Y-m-d');
                        }elseif(is_object($value)){
                            $value = $value->__toString();
                        }
                        $sheet->setCellValueExplicit(current($col) . ($i+2) , $value);

                    }else{
                        $j=0;
                        $conatctGroups =  $iterableResult->current()->$methods[$field]();
                        foreach ($conatctGroups as $conatctGroup) {
                            if ($j == 0) {
                                $value = $conatctGroup->__toString();
                            } else {
                                $value .=',' . $conatctGroup->__toString();
                            }
                            $j++;
                        }
                    }
                    next($col);
                }
                reset($col);

                $this->dm->detach($iterableResult->current());

                if($iterableResult->hasNext()){
                    $iterableResult->next();
                }else{
                    break;
                }
            }


            $phpExcelObject->setActiveSheetIndex(0);
            $this->excelObjs[] = $phpExcelObject;
        }

        }
    }

    public function build() {
        $phpExcelObject = $this->phpExcel->createPHPExcelObject();
        $phpExcelObject->getDefaultStyle()->getAlignment()->setWrapText(true);
//       $phpExcelObject->getDefaultStyle()->getFont()->setSize(15);

        $sheet = $phpExcelObject->setActiveSheetIndex(0);
        $col = range('A', 'Z');
        $methods[]=array();
        foreach($this->fields as $field){
            $methods[$field] = "get".ucfirst($field);
            $sheet->setCellValue(current($col) . "1", $this->container->get('translator')->trans($field, array(), $this->translationDomain));
            next($col);
        }
        reset($col);

        for ($i=0;$i < count($this->collection);$i++) {
            foreach($this->fields as $field){

             if ($field != 'group') {
                    if ($field == 'email' && !method_exists(current($this->collection), $methods[$field])) {
                        $value = current($this->collection)->getCreatedBy()->$methods[$field]();
                    } elseif ($field == 'married') {
                        $value = current($this->collection)->$methods[$field]() == 'Married' ? 'متزوجة' : 'آنسة';
                    } elseif ($field == 'children') {
                        $value = current($this->collection)->$methods[$field]() == 'No' ? 'لا' : 'نعم';
                    } elseif ($field == 'employee') {
                        $value = current($this->collection)->$methods[$field]() == 'No' ? 'لا' : 'نعم';
                    } elseif ($field == "gender") {
                        $value = $this->container->get('translator')->trans(current($this->collection)->$methods[$field]());
                    } else {
                        $value = current($this->collection)->$methods[$field]();
                    }
                    if (is_null($value)) {
                        $value = "";
                    } elseif ($value instanceof \DateTime) {
                        $value = $value->format('Y-m-d');
                    } elseif (is_object($value)) {
                        $value = $value->__toString();
                    }
                } else {
                    $j = 0;
                    $conatctGroups = current($this->collection)->$methods[$field]();
                    foreach ($conatctGroups as $conatctGroup) {
                        if ($j == 0) {
                            $value = $conatctGroup->__toString();
                        } else {
                            $value .= ',' . $conatctGroup->__toString();
                        }
                        $j++;
                    }
                }
                $sheet->setCellValueExplicit(current($col) . ($i + 2), $value);
                next($col);
            }
            reset($col);
            next($this->collection);
        }
        $phpExcelObject->setActiveSheetIndex(0);
//        $phpExcelObject->getActiveSheet()->setTitle($this->title);
        $this->excelObj = $phpExcelObject;

    }


    public function createResponse() {
        if(is_null($this->excelObj)){
            $this->build();
        }
        // create the writer
        $writer = $this->phpExcel->createWriter($this->excelObj, $this->writerType);
        // create the response
        $response = $this->phpExcel->createStreamedResponse($writer);
        // adding headers

        $contentType = 'application/csv; charset=utf-8';
        if($this->extension == '.xls') {
            $contentType = 'application/vnd.ms-excel; charset=utf-8';
        }

        $response->headers->set('Set-Cookie', 'fileDownload=true; path=/');
        $response->headers->set('Content-Type', $contentType);
        $response->headers->set('Content-Disposition', 'attachment;filename='.$this->title.$this->extension);
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        return $response;
    }

        public function createFileResponse() {
        if(is_null($this->excelObj)){
          $this->build();
        }

        // create the writer
        $writer = $this->phpExcel->createWriter($this->excelObj, $this->writerType);
        $filename = tempnam(sys_get_temp_dir(), 'xls-');
        $currentTime = new \DateTime();
        // create filename
        $writer->save($filename);
        $response = new BinaryFileResponse($filename);
        $response->headers->set('Set-Cookie', 'fileDownload=true; path=/');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $this->title.'['.$currentTime->format('d-m-Y').']'.$this->extension,'spreadsheet');
        return $response;
    }

    public function saveFile($fileName,$filePath=null){
        if(is_null($this->excelObj)){
          $this->build();
        }

        if(is_null($filePath)) {
            $filePath = $this->container->getParameter('xls_temp_path');
        }
        // create the writer
        $writer = $this->phpExcel->createWriter($this->excelObj, $this->writerType);
        $fileFullPath = $filePath.$fileName;
        // create filename
        $writer->save($fileFullPath);
    }

    public function saveMultipleFiles($fileName,$limit,$ext='.xls',$filePath=null){
        if(is_null($this->excelObj)){
          $this->buildMultiple($limit);
        }

        if(is_null($filePath)) {
            $filePath = $this->container->getParameter('xls_temp_path');
        }
        // create the writer
        foreach ($this->excelObjs as $index => $excelObj){
            $writer = $this->phpExcel->createWriter($excelObj, $this->writerType);
            $fileFullPath = $filePath.$index."-".$fileName.$this->extension;
            // create filename
            $writer->save($fileFullPath);
        }
    }

}
