<?php if (!defined('BOLIDO')) die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!'); ?>
<?php require(__DIR__ . '/main-header-above.tpl.php'); ?>
<div id="main-welcome">
    <h1><?php echo $this->lang->get('main_welcome_title'); ?></h1>
    <p><?php echo $this->lang->get('main_welcome_intro'); ?></p>

    <?php if (!empty($checks)) :  ?>
        <h2><?php echo $this->lang->get('main_welcome_tests'); ?></h2>
        <table>
            <?php foreach($checks as $name => $test) : ?>
            <tr>
                <td class="title"><?php echo $this->lang->get('main_test_' . $name); ?></td>
                <td class="<?php echo ($test ? 'passed' : 'failed'); ?>">
                    <?php echo $this->lang->get('main_test_' . ($test ? 'passed' : 'failed') . '_' . $name); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
<?php require(dirname(__FILE__) . '/main-footer-bottom.tpl.php'); ?>
