<?php if (!defined('BOLIDO')) die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!'); ?>

<?php
    if (!empty($this->toFooter))
    {
        foreach ($this->toFooter as $v)
        {
            echo $v;
        }
        unset($this->toFooter, $v);
    }
?>
    </body>
</html>