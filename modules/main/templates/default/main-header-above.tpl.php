<?php if (!defined('BOLIDO')) die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');?>
<!DOCTYPE html>
<html lang="<?php echo $this->lang->getCurrentLanguage(); ?>">
<head>
    <meta charset="<?php echo $this->config->charset; ?>">
    <meta name="author" content="<?php echo $this->config->siteOwner; ?>">
    <link rel="stylesheet" href="<?php echo $this->config->mainUrl;?>/Modules/main/templates/default/ss/normalize.css">
    <script type="text/javascript">var mainUrl = '<?php echo $this->config->mainUrl; ?>'; var moduleTemplateUrl = '<?php echo (!empty($moduleTemplateUrl) ? $moduleTemplateUrl : ''); ?>'; var currentModuleUrl = '<?php echo (!empty($moduleUrl) ? $moduleUrl : ''); ?>';</script>
    <script type="text/javascript" src="<?php echo $this->config->mainUrl;?>/Modules/main/templates/default/js/jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="<?php echo $this->config->mainUrl;?>/Modules/main/templates/default/js/Bolido.js"></script>
    <?php if (defined('CANONICAL_URL')) { echo '<link rel="canonical" href="' . CANONICAL_URL . '">'; } ?>
    <?php if (!empty($toHeader) && is_array($toHeader)) { echo implode('', $toHeader); } ?>
</head>
<body>
    <div id="bolido-javascript-check" style="background:#f68080;color:#570000;font-size:15px;text-align:center;padding:10px 0;border-bottom:2px solid #570000;">
        <?php echo $this->lang->get('error_enable_javascript'); ?>
    </div>
    <script type="text/javascript">
        document.getElementById('bolido-javascript-check').style.display = 'none';
        var bolidoCookieEnabled = (document.cookie.indexOf('<?php echo $this->session->getName(); ?>') != -1);
        if (!bolidoCookieEnabled) {
            document.write(unescape('<?php echo rawurlencode('<div style="background:#f68080;color:#570000;font-size:15px;text-align:center;padding:10px 0;border-bottom:2px solid #570000;">' . $this->lang->get('error_enable_cookies') . '</div>'); ?>'));
        }
    </script>
