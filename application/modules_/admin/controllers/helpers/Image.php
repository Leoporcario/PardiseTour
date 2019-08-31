<?php
require_once 'Imagenes.php';

class Zend_Controller_Action_Helper_Image extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var Zend_Loader_PluginLoader
     */
    protected $imagenes;
    protected $fileRoute;
    protected $imageRoute;
    protected $imageRouteMin;
    public $pluginLoader;
    public $validExtensions = array(
        'png',
        'PNG',
        'jpg',
        'JPG',
        'pdf',
        'PDF',
        'jpeg',
        'JPEG',
        'swf',
        'SWF',
        'rar',
        'RAR',
        'doc',
        'DOC',
        'docx',
        'DOCX',
        'xls',
        'XLS',
        'xlsx',
        'XLSX',
        'ppt',
        'PPT',
        'pps',
        'PPS',
        'pptx',
        'PPTX',
        'ppsx',
        'PPSX',
        'zip',
        'ZIP',
        'mpg',
        'MPG',
        'avi',
        'AVI',
        'mp3',
        'MP3',
        'html',
        'HTML'
        
    );
 
    /**
     * Constructor: initialize plugin loader * 
     * @return void
     */
    public function __construct()
    {
        //Routes
        $this->fileRoute = PUBLIC_PATH . DS . 'files' . DS;
        $this->imageRoute = PUBLIC_PATH . DS . 'images' . DS;
        $this->imageRouteMin = PUBLIC_PATH . DS . 'images' . DS . 'm_';
        $this->pluginLoader = new Zend_Loader_PluginLoader();
        $this->imagenes = new Model_DBTable_Imagenes();
        
    }
    
    /**
     * processImagen: recieves an upload objetc and uses its data to upload an image
     * $imageFile => image file from upload, $upload => object, $dbName => name of the image on DB
     * @return image name
     */
    public function processImage($imageFile, $upload, $dbName){
        $tmpName = explode('.', $imageFile[$dbName]["name"]);
        $newName = 'tp_' .  uniqid() . '.' .strtolower(end($tmpName));
        $extension = strtolower(end($tmpName));
        //Validate input extension => return name : return false;
        if($this->isValid($extension)){
            $upload->addFilter('Rename', array('target'=>PUBLIC_PATH . DS . 'images' . DS . $newName, 'overwrite' => true));
            if ($upload->receive($dbName)){
                $direccion_imagen = PUBLIC_PATH . DS . 'images' . DS . $newName;
                $direccion_imagen_min = PUBLIC_PATH . DS . 'images' . DS . 'm_' .$newName;
                $ancho = 375;
                //ahora llamamos a la funcion de redimensionar
                smart_resize_image($direccion_imagen, $ancho, $alto, true, $direccion_imagen_min, false, false);
            }
            $return = $newName;
        }else{
            $return = false;
        }
        return $return;
    }
    
    /**
     * processFile: recieves an upload objetc and uses its data to upload an image
     * $imageFile => image file from upload, $upload => object, $dbName => name of the image on DB
     * @return image name
     */
    public function processFile($generalFile, $upload, $dbName){
        $tmpName = explode('.', $generalFile[$dbName]["name"]);
        $extension = strtolower(end($tmpName));
        //Validate input extension => return name : return false;
        if($this->isValid($extension)){
            $newName = 'tp_' .  uniqid() . '.' . strtolower(end($tmpName));
            $upload->addFilter('Rename', array('target'=>PUBLIC_PATH . DS . 'files' . DS . $newName, 'overwrite' => true));
            if ($upload->receive($dbName)){

            }
            $return = $newName;
        }else{
            $return = false;
        }
        return $return;
    }
    
    /**
     * isValid: recieves a filename and checks if its valid extension
     * $file
     * @return bool
     */
    public function isValid($extension){
        $return = false;
        foreach($this->validExtensions as $validExtension){
            if($extension == $validExtension){
                $return = true;
            }
        }
        return $return;
    }
    
    /**
     * deleteFromTable: recieves an array of ids, if existes gets deleted
     * $idImageArray
     * @return void
     */
    public function deleteFromTable($idImageArray){
        if(count($idImageArray) > 0 ){
            foreach($idImageArray as $idImage){
                $image = $this->imagenes->get($idImage);
                $this->deleteFile($this->imageRoute . $image['Inombre']);
                $this->deleteFile($this->imageRouteMin . $image['Inombre']);
                $this->imagenes->delete_row($image['idImagen']);
            }
        }else{
            throw new Zend_Exception('No se recibio ningun id de archivo/imagen para eliminar');
        }
    }
    
    /**
     * deleteFiles: recieves a filename, if existes gets deleted
     * $filename
     * @return void
     */
    public function deleteFiles($fileArray){
        foreach($fileArray as $fileName){
            if(file_exists($fileName)){
                unlink($fileName);
            }
        }
    }
    
    /**
     * deleteFile: recieves a filename, if existes gets deleted
     * $filename
     * @return void
     */
    public function deleteFile($imageName){
        if(file_exists($imageName)){
            unlink($imageName);
        }
    }
    
    /**
     * Strategy pattern: call helper as broker method
     * 
     * @param  int $month 
     * @param  int $year 
     * @return int
     */
    public function direct($mensaje)
    {
        return $this->verificar($mensaje);
    }
}
?>
