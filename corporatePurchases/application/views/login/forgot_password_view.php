<?php    
    $backgroundImage = "assets/images/backgroundLogin.jpg";    
?>
<main class="d-flex align-items-center min-vh-100 py-3 py-md-0">
    <div class="container">
        <div class="card login-card">
            <div class="row no-gutters">
                <div class="col-md-5">
                    <img src="<?php echo base_url().$backgroundImage."?".filemtime($backgroundImage); ?>" alt="login" class="login-card-img">
                </div>
                <div class="col-md-7">
                    <div class="card-body">
                        <?php if (file_exists($logo)) { ?>
                        <div class="brand-wrapper">
                            <img src="<?php echo base_url().$logo."?".filemtime($logo); ?>" alt="logo" class="logo">
                        </div>       
                        <?php } ?> 
                        <h5>Recuperar Password</h5>                    
                        <p>Ingrese su usuario y le enviaremos el link para que pueda recuperar su password</p>       
                        <form method="post" accept-charset="utf-8" id="frmRecover" name="frmRecover" style="margin-top:10px;">
                            <div class="form-group">
                                <label for="username" class="sr-only">Usuario</label>
                                <input type="text" name="username" id="username" class="form-control" placeholder="usuario" maxlength="50" autocomplete="off" required="1" autofocus="1" value="<?php echo set_value('username'); ?>" onkeydown="cleanError();enter(event,'sendRecoverPassword()')">
                            </div>                            
                            <input name="recover" id="recover" class="btn btn-block login-btn mb-4" type="button" value="Enviar" onclick="sendRecoverPassword()" style="margin-bottom: 2px !important;">
                            <input name="back" id="back" class="btn btn-block login-btn-second mb-4" type="button" value="Volver" onclick="getHome()" style="margin-top: 2px !important;">
                        </form>
                        <nav class="login-card-footer-<?php echo ($messageCode == "OK"?"ok":"error"); ?>" id="messageLogin" style="visibility:<?php echo (isset($message) && $message != ""?"visible":"hidden")?>"><?php echo $message; ?></nav>                
                        <nav class="login-card-footer-powered-by">
                            :: powered by <strong>Solution Dealer</strong> ::                  
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>  
<?php
/* End of file login_view.php */
/* Location: ./application/views/login/login_view.php */