<?php if (!defined('BOLIDO')) die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');
  require(dirname(__FILE__) . '/main-header-above.tpl.php'); ?>
<div style="width: 550px; margin: 80px auto 0 auto;">
    <div style="background-color: #FDB812; border: 5px solid #FDB812; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;">
        <div style="background-color: #FFF; border: 1px solid #CECECE; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; padding: 25px;">
            <form action="install.php" method="post" accept-charset="UTF-8" style="margin:0 auto; padding-left: 25px; padding-right: 10px; margin-top: 10px;">
                <label class="color: #000; font-size: 14px; font-weight: bold; margin-left: 0px; margin-bottom: 5px; display: block; vertical-align: baseline;">
                    <?php echo $this->lang->get('main_install_db'); ?>
                </label>
                <input name="dbpass" type="password" style="font-size: 15px; color: #333; padding: 5px; margin-bottom: 20px; border:1px solid #d3d3d3; background-color: #FFF; clear: both;" />
                <input value="go" type="<?php echo $this->lang->get('common_send_form'); ?>" />
            </form>
        </div>
    </div>
</div>
<?php require(dirname(__FILE__) . '/main-footer-bottom.tpl.php'); ?>
