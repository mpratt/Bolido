<?php if (!defined('BOLIDO')) die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->config->get('language'); ?>" lang="<?php echo $this->config->get('language'); ?>" dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->config->get('charset'); ?>" />
    <meta http-equiv="Author" content="<?php echo $this->config->get('siteOwner'); ?>" />
    <title><?php echo $htmlTitle; unset($htmlTitle); ?></title>
    <script type="text/javascript">var mainurl = '<?php echo $this->config->get('mainurl'); ?>';<?php if (!empty($moduleTemplateUrl)) : ?>var moduletemplateurl = '<?php echo $moduleTemplateUrl; ?>';<?php endif; ?><?php if (!empty($moduleUrl)) : ?>var currentmoduleurl = '<?php echo $moduleUrl; ?>'; <?php endif; ?></script>
    <?php if (!$htmlIndexing) : ?><meta name="robots" content="noindex, nofollow, noimageindex, noarchive" /><?php unset($HtmlIndexing); endif; ?>
    <?php if (!empty($htmlDescription)) : ?><meta name="description" content="<?php echo $htmlDescription;?>"><?php unset($htmlDescription); endif; ?>
    <?php
        if (!empty($this->toHeader))
        {
            foreach ($this->toHeader as $v)
            {
                echo $v;
            }
            unset($this->toHeader, $v);
        }
    ?>
</head>
<body>