<?php
//Modelos
require_once 'Newsletters.php';
require_once 'News.php';
require_once 'NewsletterNew.php';
//Librerias
require_once 'Common.php';

class Admin_NewsletterController extends Zend_Controller_Action {

    var $fields = array(
        array('field' => 'IDNewsletter', 'label' => 'N° Newsletter', 'list' => true, 'width' => 120, 'class' => 'id', 'order' => true),
        array('field' => 'NLName', 'label' => 'Nombre', 'required' => true, 'list' => true, 'search' => true, 'order' => true),
        array('field' => 'NLBody', 'label' => 'Cuerpo', 'required' => false, 'list' => false, 'search' => false, 'order' => false),
        array('field' => 'NLHtml', 'label' => 'Html', 'required' => false, 'list' => false, 'search' => false, 'order' => false)
    );
    var $actions = array(
        array('type' => 'link', 'label' => 'Generar nuevo newsletter', 'icon' => 'plus', 'controller' => 'newsletter', 'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todos los newsletters enviados', 'icon' => 'list', 'controller' => 'newsletter', 'action' => 'index'),
        array('type' => 'separator'),
        array('type' => 'link', 'label' => 'Enviar newsletter', 'icon' => 'forward', 'controller' => 'send', 'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todos los envios realizados', 'icon' => 'list', 'controller' => 'send', 'action' => 'index'),       
    );
    var $options = array(
        array('title' => 'Ver en la web', 'icon' => 'eye-open text-primary', 'action' => 'view', 'target' => '_blank'),
        array('title' => 'Eliminar', 'icon' => 'ban-circle text-danger', 'action' => 'delete'),
    );
    var $newsletters;
    var $newsletterNew;
    var $news;

    public function init() {
        //Datos de login y sesion
        $options = array(
            'layout' => 'admin',
            'layoutPath' => '../application/layouts/scripts/'
        );
        Zend_Layout::startMvc($options);
        $storage = new Zend_Auth_Storage_Session();
        $data = $storage->read();
        if (!$data) {
            $this->_redirect('/admin/login');
        }
        $this->view->ULoginname = $data->ULoginname;
        $this->view->UName = $data->UName;
        $this->view->ULastname = $data->ULastname;
        $this->view->IDUser = $data->IDUser;

        //Vars and params
        $this->parameters = $this->_request->getParams();
        $this->view->parameters = $this->parameters;
        $this->view->controller = $this->_getParam('controller');
        $this->view->menuCurrent = 'newsletter';
        $this->view->currentIcon = 'glyphicon glyphicon-bullhorn';
        $this->view->currentBrand = 'Newsletter';

        //Messages (error and success)
        $this->errorMessage = 'Ocurrio algun error en la ejecución de la transacción.';
        $this->warningMessage = 'Se intento crear un newsletter sin noticias seleccionadas.';
        $this->newSuccessMessage = '<b>Información:</b> Newsletter generado con éxito!';
        $this->editSuccessMessage = '<b>Información:</b> Newsletter editado con éxito!';
        $this->deleteSuccessMessage = '<b>Información:</b> Newsletter eliminado con éxito!';
        $this->cancelledMessage = '<b>Información:</b> Transacción cancelada.';

        //Fields, actions, options
        $this->view->fields = $this->fields;
        $this->view->actions = $this->actions;
        $this->view->options = $this->options;

        //Helpers
        $this->response = $this->getResponse();
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->messages = $this->_helper->flashMessenger->getMessages();
        $statusBar = $this->_getParam('status');
        $this->view->statusBar = $statusBar;

        //Partials
        $this->response->insert('menuLogin', $this->view->render('/menuLogin.phtml'));
        $this->response->insert('paginationControl', $this->view->render('/paginationControl.phtml'));

        //Modelos
        $this->newsletters = new Model_DBTable_Newsletters();
        $this->newsletterNew = new Model_DBTable_NewsletterNew();
        $this->news = new Model_DBTable_News();
        $this->form = new Zend_Form();

        //Extra scripts
        $this->view->headScript()->appendFile($this->view->baseUrl() . "/js/Form.js");
    }

    public function indexAction() {
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if ($this->form->isValid($post)) {
                $statusBar = 'info';
                $this->view->search = $this->getRequest()->getPost('search', '');
                $where = create_where($this->getRequest()->getPost('search', ''), $this->fields);
            }
        }
        try {
            $results_all = $this->newsletters->showAll($where, $this->parameters['sort'], $this->parameters['order']);
            $paginator = Zend_Paginator::factory($results_all);
            $paginator->setItemCountPerPage(COUNTPERPAGE)
                    ->setCurrentPageNumber($this->_getParam('page', 1))
                    ->setPageRange(PAGERANGE);

            $this->view->results = $paginator;
            $this->view->enableSearch = true;
        } catch (Zend_Exception $exc) {
            echo $exc->getMessage();
            exit();
        }

        //Sending params and vars to view
        $this->view->messages = $this->messages;
        if($statusBar){
            $this->view->statusBar = $statusBar;            
        }
    }

    public function addAction() {
        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($this->getRequest()->getPost())) {
                //Elimino submit y otros no utiles
                unset($_POST['submit']);
                $idNoticias = $_POST['IDNoticias'];
                unset($_POST['IDNoticias']);
                
                try{
                    if(count($idNoticias) > 0){
                        $IDNewsletter = $this->newsletters->add($_POST);
                        $newsletter = $this->newsletters->get($IDNewsletter);
                        foreach ($idNoticias as $IDNoticia) {
                            $noticia = $this->news->get($IDNoticia);
                            $this->newsletterNew->add(array('IDNewsletter' => $IDNewsletter, 'IDNoticia' => $IDNoticia, 'NLDate' => $noticia['NFecha']));
                        }

                        $cuerpo = '<html xmlns="http://www.w3.org/1999/xhtml">
                        <head>
                            <title>Federacion Económica Mendoza</title>  
                            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        </head>';

                        $cuerpo .=
                                '<body>
                            <div style="margin:auto; width:700px; background:#fff; font-family:calibri;">
                            <table cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td height="150" width="25%">

                                    </td>
                                    <td style="background-color:#fff; color:#fff; text-align: center;" valign="middle" style="text-align: center;">

                                        <br>
                                        <img src="' . HOST . $this->view->baseUrl() . '/imgs/logo.png"/>
                                    </td>
                                    <td width="15%">

                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="10" style="background:#fff;">
                                        <hr width="80%" style="opacity:0.2;">
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="10" style="text-align: center; background:#fff; color:#666;">
                                        <font size="4" face="Calibri" >
                                            <strong>
                                                Newsletter - ' . $newsletter['NLName'] . '
                                            </strong>
                                        </font>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="10" style="background:#fff;">
                                        <hr width="80%" style="opacity:0.2;">
                                    </td>
                                </tr>';

                        $idNoticias = $this->newsletterNew->showAll($IDNewsletter, 'NLDate DESC');

                        foreach ($idNoticias as $idNoticia) {
                            $noticias[] = $this->news->get($idNoticia);
                        }

                        foreach ($noticias as $noticia) {
                            $cuerpo .=
                                    '<tr>
                                        <td colspan="10" style="background:#fff;">
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="color:#333;">
                                                <tr>
                                                    <td colspan="10" height="30" style="background:#4E5D9F; font-size: 14px; font-weight: bold; text-align: right; padding-right: 20px; color:#fff">
                                                        ' . $noticia['NTitulo'] . '
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="70%" valign="top" style="padding:5px;">
                                                        <font color="#888" size="2">
                                                            ' . invert_date($noticia['NFecha'], 'spanish') . ' 
                                                        </font>
                                                        <p style="font-size: 12px;">
                                                           ' . $noticia['NIntroduccion'] . '
                                                        </p>
                                                    </td>
                                                    <td width="30%">
                                                        <img width="100%" src="' . HOST . $this->view->baseUrl() . '/images/m_' . $noticia['NImagen'] . '">                                                           
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>';
                        }
                        $cuerpo .=
                                '
                                <tr>
                                    <td colspan="3" style="background:#FFF; color:#4E5D9F; padding-left:10px;" height="30">
                                        <a href="http://www.femza.org.ar" style="text-decoration: none; Color:#4E5D9F; ">
                                            FEM - Version Online
                                        </a>
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        &nbsp;&nbsp;&nbsp;&nbsp;            
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        &nbsp;&nbsp;&nbsp;&nbsp;            
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        &nbsp;&nbsp;&nbsp;&nbsp;                
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        &nbsp;&nbsp;&nbsp;&nbsp;            
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        &nbsp;&nbsp;&nbsp;&nbsp;            
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        &nbsp;&nbsp;&nbsp;&nbsp;                
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        &nbsp;&nbsp;&nbsp;&nbsp;                                
                                    </td>
                                </tr>
                            </table>
                        </div>
                        </body>';

                        $this->view->message = 'Newsletter creado con exito!';
                        //GENERAR HTML

                        $body = $cuerpo;
                        $html = $body;
                        $html_code = $html . '</html>';
                        $html_name = "newsletter_" . $newsletter['IDNewsletter'] . ".html";
                        $path_html = "newsletters/" . $html_name;
                        $Open = fopen($path_html, "a+");
                        if ($Open) {
                            fwrite($Open, $html_code);
                        }

                        $this->newsletters->edit(array('IDNewsletter' => $newsletter['IDNewsletter'], 'NLHtml' => $html_name, 'NLBody' => $html_code));
                    }else{
                        //Redirecciono al add por no haber seleccionado noticias  
                        $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->warningMessage));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                    }
                } catch (Zend_Exception $exc) {
                    //Redirecciono al index con error. Evita colgar la aplicación
                    $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => $this->errorMessage));
                    $this->_redirector->gotoSimple('index', null, null, array('status' => 'danger'));
                }
            }
            //Manejo avisos y redirecciono
            $this->_helper->flashMessenger->addMessage(array('type' => 'success', 'message' => $this->newSuccessMessage));
            $this->_redirector->gotoSimple('index', null, null, array('status' => 'success'));
        }

        $this->view->news = $this->news->showAll();

        //idFormulario para validacion
        $this->view->formId = 'newsletterForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'plus';
        $this->view->title = 'Generar nuevo Newsletter';
        $this->view->description = 'Complete el formulario para agregar un newsletter listo para enviar';

        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
    }

    public function viewAction() {
        $this->_helper->layout()->disableLayout();

        $IDNewsletter = $this->_getParam('id', '');
        $this->view->newsletter = $this->newsletters->get($IDNewsletter);
        $idNoticias = $this->newsletterNew->showAll($IDNewsletter, 'NLDate DESC');

        foreach ($idNoticias as $idNoticia) {
            $noticias[] = $this->news->get($idNoticia);
        }

        $this->view->noticias = $noticias;
    }

    public function deleteAction() {
        $id = $this->_getParam('id', 0);
        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($this->getRequest()->getPost())) {
                $delete_request = $this->getRequest()->getPost('delete_request');

                if ($delete_request == "Yes") {
                    try {
                        $this->newsletterNew->delete_row_by_newsletter($id);
                        $this->view->result = $this->newsletters->delete_row($id);
                    } catch (Zend_Exception $exc) {
                        $this->_helper->flashMessenger->addMessage(array('type' => 'error', 'message' => $this->errorMessage));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'danger'));
                    }
                    $this->_helper->flashMessenger->addMessage(array('type' => 'success', 'message' => $this->deleteSuccessMessage));
                    $this->_redirector->gotoSimple('index', null, null, array('status' => 'success'));
                } else if ($delete_request == "No") {
                    $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->cancelledMessage));
                    $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                }
            }
        }
        if ($id > 0) {
            $this->view->result = $this->newsletters->get($id);
        } else {
            $this->_redirect('admin/newsletter/');
        }
        
        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'ban-circle';
        $this->view->title = 'Eliminar newsletter';
        $this->view->description = 'Seleccione una opción';
    }
}

