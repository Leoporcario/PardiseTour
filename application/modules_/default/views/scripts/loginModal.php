<!-- loginModal -->
<div class="modal fade bs-modal-lg" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row visible-xs">
                    <div class="col-xs-11 text-center">
                        <img src="<?php echo $this->baseUrl() ?>/images/logo.png"/>                                
                    </div>
                    <div class="col-xs-1 text-right imgsModal">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="<?php echo $this->baseUrl() ?>/images/modal-x.png"/></button>
                    </div>
                </div>
                <div class="row visible-md visible-sm">
                    <div class="col-md-4 col-sm-4 col-xs-4 logo text-center">
                        <img src="<?php echo $this->baseUrl() ?>/images/logo.png"/>
                    </div>
                    <div class="col-md-8 col-sm-8 col-xs-8 text-right imgsModal">
                        <img src="<?php echo $this->baseUrl() ?>/images/modal-img-2.jpg"/>
                        <img src="<?php echo $this->baseUrl() ?>/images/modal-img-3.jpg"/>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="<?php echo $this->baseUrl() ?>/images/modal-x.png"/></button>
                    </div>
                </div>
                <div class="row visible-lg">
                    <div class="col-lg-4 logo text-center">
                        <img src="<?php echo $this->baseUrl() ?>/images/logo.png"/>                                
                    </div>
                    <div class="col-lg-8 text-center pull-right imgsModal">
                        <img src="<?php echo $this->baseUrl() ?>/images/modal-img-3.jpg"/>
                        <img src="<?php echo $this->baseUrl() ?>/images/modal-img-2.jpg"/>
                        <img src="<?php echo $this->baseUrl() ?>/images/modal-img-1.jpg"/>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="<?php echo $this->baseUrl() ?>/images/modal-x.png"/></button>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-6">
                        <h1 class="color-b thin text-shadow-b">
                            Welcome
                        </h1>
                        <p class="color-b">
                            This webpage is a non-commercial one, designed to be consulted by both the public in general and the professionals from the tourism industry, with the goal of satisfying any need of information they might have. Paradise Tours wishes thereby to give a boost to travel sales to French Polynesia from around the world, helping everyone involved in the destination's industry in the process.
                        </p>
                    </div>
                    <div class="col-lg-6">
                        <h1 class="color-a thin text-shadow-a">
                            Bienvenido
                        </h1>
                        <p class="color-a">
                            Esta es una página web no comercial, diseñada para ser consultada tanto por los profesionales del turismo como por el público en general, con el objetivo de satisfacer cualquier necesidad de información que éstos pudieran tener. Paradise Tours desea de esta manera dar un impulso a las ventas de viajes a Polinesia Francesa desde todo el mundo, ayudando en el proceso a todos los involucrados en la industria de este destino.
                        </p>
                    </div>
                </div>
                <div class="clearfix" style="margin-top: 15px;">

                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <a href="#" class="bold color-b title2">
                            <div class="col-lg-4 text-right" style="padding-right: 15px;">
                                <img src="<?php echo $this->baseUrl() ?>/images/modal-eng.png"/>
                            </div>
                            <div class="col-lg-8" style="padding-left: 5px; padding-top: 7px">
                                Click to continue<br>
                                <small class="thin title5">(No need to register)</small>
                            </div>
                        </a>
                    </div>                         
                    <div class="col-lg-6">
                        <a href="#" class="bold color-a title2">                                    
                            <div class="col-lg-4 text-right" style="padding-right: 15px;">
                                <img src="<?php echo $this->baseUrl() ?>/images/modal-spain.png"/>
                            </div>
                            <div class="col-lg-8" style="padding-left: 5px; padding-top: 7px">
                                Click para seguir<br>
                                <small class="thin title5">(No necesitas registrarte)</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-lg-3 pull-right text-muted">
                        <a href="#" class="text-muted">English</a> | <a href="#">Español</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 text-left">
                        <h2 class="title1 thin">User area</h2>
                        <h7 class="light title3">Sign up</h7>
                    </div>
                    <div class="col-lg-9 text-right">
                        <form class="form-inline" method="get" role="form">
                            <div class="form-group">
                                <input type="text" name="username" class="form-control" placeholder="User">       
                            </div>
                            <div class="form-group">
                                <input type="password" name="password" class="form-control" placeholder="Password">        
                            </div>
                            <div class="form-group"> 
                                <button type="submit" class="btn btn-info btn-primary-b">Sign up</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 text-left">
                        or <a href="#" class="bold color-a">Create account</a>
                    </div>
                    <div class="col-lg-4 text-left">
                        <div class="checkbox">
                            <label>
                                Remember me
                                <input type="checkbox"/>
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-3 text-left">
                        <a href="#" class="light color-b decorated">Forgot password</a>
                    </div>
                    <div class="col-lg-2 text-left">

                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 text-center light text-muted">
                        <br>
                        <small>Paradise Tours Tahiti · Spirit of Polynesia ® 2014</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end loginModal -->