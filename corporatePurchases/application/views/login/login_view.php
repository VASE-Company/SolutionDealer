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
                        <?php } else { ?>
                        <div class="login-card-title">
                            Bienvenid@!
                        </div>
                        <?php } ?>   
                        <form method="post" accept-charset="utf-8" id="frmLogin" name="frmLogin">
                            <div class="form-group">
                                <label for="username" class="sr-only">Usuario</label>
                                <input type="text" name="username" id="username" class="form-control" placeholder="usuario" maxlength="50" required="1" autofocus="1" value="<?php echo set_value('username',$username); ?>" onkeydown="cleanError();enter(event,'sendLogin()')">
                            </div>
                            <div class="form-group mb-4">
                                <label for="password" class="sr-only">Password</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="password" maxlength="25" autocomplete="off" required="1" value="" onkeydown="cleanError();enter(event,'sendLogin()')">
                            </div>
                            <input name="login" id="login" class="btn btn-block login-btn mb-4" type="button" value="Ingresar" onclick="sendLogin()">
                        </form>
                        <a href="<?php echo base_url(); ?>main/forgotPassword" class="forgot-password-link">Olvidé mi clave?</a>                        
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