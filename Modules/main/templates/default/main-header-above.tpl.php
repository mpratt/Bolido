<?php if (!defined('BOLIDO')) die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->config->get('language'); ?>" lang="<?php echo $this->config->get('language'); ?>" dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->config->get('charset'); ?>" />
    <meta http-equiv="Author" content="<?php echo $this->config->get('siteOwner'); ?>" />
    <script type="text/javascript">var mainurl = '<?php echo $this->config->get('mainurl'); ?>';<?php if (!empty($moduleTemplateUrl)) : ?>var moduletemplateurl = '<?php echo $moduleTemplateUrl; ?>';<?php endif; ?><?php if (!empty($moduleUrl)) : ?>var currentmoduleurl = '<?php echo $moduleUrl; ?>'; <?php endif; ?></script>
    <?php
        if (!empty($toHeader))
        {
            foreach ($toHeader as $v)
            {
                echo $v;
            }
            unset($this->toHeader, $v);
        }
    ?>
</head>
<body>
    <div id="js-check" style="background:#f68080;color:#570000;font-size:15px;text-align:center;padding:10px 0;border-bottom:2px solid #570000;">
        <?php echo $this->lang->get('error_enable_javascript'); ?>
    </div><script type="text/javascript">document.getElementById('js-check').style.display = "none";</script>