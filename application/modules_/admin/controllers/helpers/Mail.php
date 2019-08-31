<?php
//require_once '../application/modules/visor/models/DbTable/Alert.php';
require_once 'PHPMailer/class.phpmailer.php';

class Zend_Controller_Action_Helper_Mail extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var Zend_Loader_PluginLoader
     */
    public $pluginLoader;
    private $host = 'localhost';
    private $port = 25;
    private $username = 'info@femza.org.ar';
    private $password = 'femza';
    private $email = 'info@femza.org.ar';
 
    /**
     * Constructor: initialize plugin loader * 
     * @return void
     */
    public function __construct()
    {
        $this->pluginLoader = new Zend_Loader_PluginLoader();
    }
    
    public function sendEmail($contenido, $asunto){    
        $this->mail = new PHPMailer();
            $this->mail->IsSMTP();
            $this->mail->SMTPAuth = true;
            $this->mail->Host = $this->host;
            $this->mail->Port = $this->port;
            $this->mail->Username = $this->username;
            $this->mail->Password = $this->password;
            $this->mail->AddReplyTo($this->email, $this->email);
            $this->mail->AddAddress($this->email, $this->email);
            $this->mail->SetFrom($this->email, $this->email);
            $this->mail->Subject = 'FEM - ' . $asunto;  
            $this->mail->MsgHTML($contenido);
            $this->mail->Send();
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
