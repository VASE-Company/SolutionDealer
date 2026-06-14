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
                        <p>Ingrese su nuevo password</p>  
                        <form method="post" accept-charset="utf-8" id="frmReset" name="frmReset" style="margin-top:10px;">
                            <div class="form-group mb-4">
                                <label for="newPassword" class="sr-only">Nuevo Password</label>
                                <input type="password" name="newPassword" id="newPassword" class="form-control" placeholder="nuevo password" maxlength="25" autocomplete="off" autofocus="1" required="1" value="" onkeydown="cleanError();enter(event,'sendResetPassword()')">
                            </div>
                            <div class="form-group mb-4">
                                <label for="confirmationPassword" class="sr-only">Confirme su Password</label>
                                <input type="password" name="confirmationPassword" id="confirmationPassword" class="form-control" placeholder="confirme su password" maxlength="25" autocomplete="off" required="1" value="" onkeydown="cleanError();enter(event,'sendResetPassword()')">
                            </div>                    
                            <input class="btn btn-block login-btn mb-4" type="button" value="Enviar" onclick="sendResetPassword()">
                            <input type="hidden" id="key" name="key" value="<?php echo set_value('key',$key); ?>">
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