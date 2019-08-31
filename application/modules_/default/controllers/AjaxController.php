<?php

require_once '../application/models/DbTable/Campaigns.php';
require_once '../application/models/DbTable/Comments.php';
require_once '../application/models/DbTable/Suscriptors.php';
require_once '../application/models/DbTable/News.php';
require_once '../application/models/DbTable/Countries.php';
require_once '../application/models/DbTable/Calendar.php';
require_once 'Common.php';

class AjaxController extends Zend_Controller_Action {

    var $model_campaign;
    var $model_calendar;
    var $model_comment;
    var $model_new;
    var $model_suscriptor;
    var $model_pais;
    
    public function init() {
        $options = array(
            'layout' => 'default',
            'layoutPath' => '../application/layouts/scripts/'
        );
        /*
          $this->view->BMatricula = $data->BMatricula;
          $this->view->BNombre = $data->BNombre;
          $this->view->BApellido = $data->BApellido;
         */
        $this->response = $this->getResponse();
        $this->view->parameters = $this->_request->getParams();
        //$this->response->insert('menuLogin', $this->view->render('/menuLogin.phtml'));
        /* Initialize action controller here */
        $this->model_campaign = new Model_DBTable_Campaigns();
        $this->model_comment = new Model_DBTable_Comments();
        $this->model_suscriptor = new Model_DBTable_Suscriptors();
        $this->model_new = new Model_DBTable_News();
        $this->model_pais = new Model_DBTable_Countries();
        $this->model_calendar = new Model_DBTable_Calendar();        
        Zend_Session::start();
    }

    public function changelngAction(){
        $this->_session = $this->_helper->getHelper('PublicUser');
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_session->setLang($this->parameters['lang']);
        echo '<pre>';
        print_r($this->view->parameters);
        echo '</pre>';
        exit();
    }
    public function calendarAction() {
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(true);
        $this->view->headScript()->appendFile($this->view->baseUrl() . "/ajax_calendar/calendar.js");
        $this->view->headScript()->appendFile($this->view->baseUrl() . "/js/Common.js");
        $this->view->headScript()->appendFile($this->view->baseUrl() . "/js/jQuery.js");
        
        $eventos = $this->model_calendar->showAllDate('','','CaDate DESC');
        
        $month = $this->_getParam('month', '');
        $year = $this->_getParam('year', '');  
        
        $output = ''; 
        
        /*     
        $month = $_GET['month'];
        $year = $_GET['year'];
         */

        if($month == '' && $year == '') { 
                $time = time();
                $month = date('n',$time);
            $year = date('Y',$time);
        }  
        
        $date = getdate(mktime(0,0,0,$month,1,$year));
        $today = getdate();
        $hours = $today['hours'];
        $mins = $today['minutes'];
        $secs = $today['seconds'];

        if(strlen($hours)<2) $hours="0".$hours;
        if(strlen($mins)<2) $mins="0".$mins;
        if(strlen($secs)<2) $secs="0".$secs;

        $days=date("t",mktime(0,0,0,$month,1,$year));
        $start = $date['wday']+1;
        $name = $date['month'];
        $year2 = $date['year'];
        $offset = $days + $start - 1;

        if($month==12) { 
                $next=1; 
                $nexty=$year + 1; 
        } else { 
                $next=$month + 1; 
                $nexty=$year; 
        }

        if($month==1) { 
                $prev=12; 
                $prevy=$year - 1; 
        } else { 
                $prev=$month - 1; 
                $prevy=$year; 
        }

        if($offset <= 28) $weeks=28; 
        elseif($offset > 35) $weeks = 42; 
        else $weeks = 35; 
        
        if($_SESSION['lang'] == 'E'){
            $lunes = 'Mon';
            $martes = 'Tue';
            $miercoles = 'Wen';
            $jueves = 'Thu';
            $viernes = 'Fri';
            $sabado = 'Sat';
            $domingo = 'Sun';
        }else{
            $lunes = 'Lun';
            $martes = 'Mar';
            $miercoles = 'Mie';
            $jueves = 'Jue';
            $viernes = 'Vie';
            $sabado = 'Sab';
            $domingo = 'Dom';
        }
        
        if($_SESSION['lang'] == 'E'){
            $lenguaje = '';
        }else{
            $lenguaje = 'spa';
        }
        
        $output .= "
        <table class='cal' style='width:100%; text-align:center; color:#ddd' cellspacing='1'>
        <tr>
                <td colspan='7'>
                        <table class='calhead'>
                        <tr>
                                <td>
                                        <a href='javascript:navigate($prev,$prevy)'><img src='". $this->view->baseUrl() ."/ajax_calendar/calLeft.gif'></a> <a href='javascript:navigate(\"\",\"\")'><img src='". $this->view->baseUrl() ."/ajax_calendar/calCenter.gif'></a> <a href='javascript:navigate($next,$nexty)'><img src='". $this->view->baseUrl() ."/ajax_calendar/calRight.gif'></a>
                                </td>
                                <td width='270' align='center' style='text-align:right;'>
                                    <div style='color:#fff;'>" . month_in_letters($date['mon'], $lenguaje) . ' ' . $year2 . "</div>
                                </td>
                        </tr>
                        </table>
                </td>
        </tr>
        <tr class='dayhead' style='color:#000'>
                <td width='14%'>" . $domingo . "</td>
                <td width='14%'>" . $lunes . "</td>
                <td width='14%'>" . $martes . "</td>
                <td width='14%'>" . $miercoles . "</td>
                <td width='14%'>" . $jueves . "</td>
                <td width='14%'>" . $viernes . "</td>
                <td width='14%'>" . $sabado . "</td>
        </tr>";

        $col=1;
        $cur=1;
        $next=0;

        for($i=1;$i<=$weeks;$i++) {  
                if($next==3) $next=0;
                if($col==1) $output.="<tr class='dayrow'>"; 
                /*
                echo $cur;
                echo '<br>';
                echo $name;
                */
                $output.="<td valign='top' onMouseOver=\"this.className='dayover'\" onMouseOut=\"this.className='dayout'\">";

                if($i <= ($days+($start-1)) && $i >= $start) {
                        $output.="<div class='day'";
                        
                        foreach($eventos as $evento){
                            $mensaje = $evento['CaTitle'];
                            $mes = month_in_letters($evento['mes'], '');
                            if(($cur==$evento['dia']) && ($name==$mes) && ($year==$evento['anio'])) $output.=" style='border-radius:4px; background-color:#999; color:#000; cursor:pointer;' onclick='showDiv(". $evento['IDCalendar'] .")'";
                        }
                        
                        //if(($cur==$today[mday]) && ($name==$today[month])) $output.=" style='color:#5CAFD5'";

                        $output.=">$cur</div></td>";

                        $cur++; 
                        $col++; 

                } else { 
                        $output.="&nbsp;</td>"; 
                        $col++; 
                }  

            if($col==8) { 
                    $output.="</tr>"; 
                    $col=1; 
            }
        }

        $output.="</table>";

        echo $output;
        exit();
    }

}
