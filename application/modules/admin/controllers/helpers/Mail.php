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
    
    public function sendEmail($contenido, $asunto,$to='inbound@tahitiparadise.com'){ 
        // $headers = "From: inbound@tahitiparadise.com\r\n";
        // $headers .= "Reply-To: inbound@tahitiparadise.com\r\n";
        // $headers .= "MIME-Version: 1.0\r\n";
        // $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        
        // // $subject = ($this->lang == 'Es') ? 'Tahiti Paradise - Consulta desde nuestra web' : 'Tahiti Paradise - Message sent from our web' ;
        // // $body = '<html><body>';
        // // $body .= "<strong style='color:#2D439C'>Tahiti Paradise - Mensaje enviado desde la web. </strong><br />";
        // // $body .= "Enviado por: <strong>" . $_POST['SNombre'] . "</strong> | Email : <strong>" . $_POST['SEmail'] . "</strong><br />";
        // // $body .= "Empresa: " . (isset($_POST['SEmpresa']) && $_POST['SEmpresa'] != '') ? $_POST['SEmpresa'] : 'No indica.' ;
        // // $body .= "<br />";
        // // $body .= "Telefono: " . (isset($_POST['STelefono']) && $_POST['STelefono'] != '') ? $_POST['STelefono'] : 'No indica.' ;
        // // $body .= "<br />";
        // // $body .= "<strong>Consulta: </strong>" . $_POST['SConsulta'];
        // // $body .= '</body></html>';
        
        // //Envio el email
        // mail(
        //     $to, 
        //     $asunto, 
        //     $contenido, 
        //     $headers
        //     );
        // // mail()   
        // $this->mail = new PHPMailer();
        // $this->mail->IsSMTP();
        // $this->mail->SMTPAuth = true;
        // $this->mail->Host = $this->host;
        // $this->mail->Port = $this->port;
        // $this->mail->Username = $this->username;
        // $this->mail->Password = $this->password;
        // $this->mail->AddReplyTo($this->email, $this->email);
        // $this->mail->AddAddress($this->email, $this->email);
        // $this->mail->SetFrom($this->email, $this->email);
        // $this->mail->Subject = 'FEM - ' . $asunto;  
        // $this->mail->MsgHTML($contenido);
        // $this->mail->Send();
        $mail = new PHPMailer(); // create a new object
        $mail->IsSMTP(); // enable SMTP
        $mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
        $mail->SMTPAuth = true; // authentication enabled
        // $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
        $mail->Host = "mail.tahitiparadise.com";
        $mail->Port = 587; // or 587
        $mail->IsHTML(true);
        $mail->Username = "info@tahitiparadise.com";
        $mail->Password = "Ramyap001";
        $mail->SetFrom("inbound@tahitiparadise.com","Paradise Tours");
        $mail->Subject = $asunto;
        $mail->MsgHTML($contenido);
        $mail->AddAddress($to);
        // $mail->AddBCC("mdampuero@gmail.com", "Prueba");
        if(!$mail->Send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
            echo "Message has been sent";
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
