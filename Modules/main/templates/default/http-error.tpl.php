<?php if (!defined('BOLIDO')) die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');
  require(dirname(__FILE__) . '/main-header-above.tpl.php'); ?>
<div style="width: 550px; margin: 80px auto 0 auto;">
    <div style="background-color: #FDB812; border: 5px solid #FDB812; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;">
        <div style="background-color: #FFF; border: 1px solid #CECECE; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; padding: 25px;">
            <h1 style="font-size: 400%; line-height: 1em; margin: 0; padding: 0;">
                <?php echo $code; ?>
            </h1>
            <h2 style="font-size: 120%; margin: 0; padding: 0;">
                <?php echo $message; ?>
            </h2>
            <p><a href="<?php echo $this->config->mainUrl; ?>"><?php echo $this->lang->get('common_go_back_extended'); ?></a></p>
        </div>
    </div>
</div>
<?php require(dirname(__FILE__) . '/main-footer-bottom.tpl.php'); ?>
