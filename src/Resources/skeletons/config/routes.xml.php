<?php echo '<?'; ?>xml version="1.0" encoding="UTF-8" ?>
<routes xmlns="http://symfony.com/schema/routing"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://symfony.com/schema/routing
        https://symfony.com/schema/routing/routing-1.0.xsd">

    <?php if ($withStorefront || $withAdmin) { ?>
    <import resource="../../Controller/**/*Controller.php" type="attribute" />
    <?php } ?>
    <import resource="../../Route/**/*Route.php" type="attribute" />
</routes>
