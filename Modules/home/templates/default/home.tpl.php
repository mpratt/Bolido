<?php if (!defined('BOLIDO')) die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!'); ?>

<div class="home-container">
    <div class="home-secondary">
        <div class="front-slogan">
            <p>
                <img style="float:left;padding-right:8px;" src="<?php echo $moduleTemplateUrl; ?>/im/me.jpg" alt="" />
                <?php echo $this->lang->get('home_front_text1'); ?>
            </p>

            <p><?php echo $this->lang->get('home_front_text2'); ?></p>
            <p><?php echo $this->lang->get('home_front_text3', '<a href="http://www.slackware.com" target="_blank">Slackware</a>', '<a href="https://github.com/mpratt" target="_blank">Github</a>', '<a href="http://www.facebook.com/profile.php?id=534050311" target="_blank">Facebook</a>', '<a href="http://stackoverflow.com/users/430087/pratt" target="_blank">StackOverflow</a>', '<a href="' . $this->config->get('mainurl') . '/contacto/" target="_blank">contactar</a>'); ?></p>
            <p><?php echo $this->lang->get('home_users_online', $userOnline); ?></p>
        </div>

        <div class="preview-box">
            <ul class="entries-list">
                <li class="title">
                    <span class="sprite-image"></span>
                    <?php echo $this->lang->get('home_albums'); ?>
                </li>
                <?php if (!empty($galleries)) : foreach($galleries as $gal) : ?>
                <li>
                    <div class="album">
                        <a href="<?php echo $this->config->get('mainurl'); ?>/galeria/<?php echo $gal['gallery_id']; ?>/">
                            <img src="<?php echo $gal['gallery_thumb']; ?>" alt="<?php echo $gal['gallery_name']; ?>" />
                        </a>
                    </div>
                </li>
                <?php endforeach; endif;?>
            </ul>
            <div class="clearfix"></div>
        </div>
        <br />

        <div class="preview-box">
            <ul class="entries-list">
                <li class="title">
                    <span class="sprite-blog"></span>
                    <?php echo $this->lang->get('home_blog_entries'); ?>
                </li>
                <?php if (!empty($blogEntries)) : foreach($blogEntries as $entries) : ?>
                <li>
                    <span><?php echo date('M. d', strtotime($entries['published_date']));?></span>
                    <a href="<?php echo $this->config->get('mainurl'); ?>/blog/<?php echo $entries['entry_id']; ?>/<?php echo $entries['url']; ?>/">
                        <?php echo $entries['title']; ?>
                    </a>
                </li>
                <?php endforeach; endif;?>
            </ul>
        </div>
    </div>

    <div class="home-lists">
        <ul id="lifestream" class="entries-list">
            <li class="title">
                <span class="sprite-globe-green"></span>
                <?php echo $this->lang->get('home_social_activities'); ?>
            </li>
            <li id="loader">
                <img src="<?php echo $this->config->get('templateurl'); ?>/im/loader-bar.gif" alt="" />
            </li>
        </ul>
    </div>
</div>

<div class="clearfix"></div>
<p>&nbsp;</p>

