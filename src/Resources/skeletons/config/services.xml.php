<?php echo '<?'; ?>xml version="1.0" ?>

<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <services>
        <defaults autowire="true" autoconfigure="true" public="false"/>

        <?php if ($additionalBundleName) { ?>
        <prototype namespace="<?php echo $namespace; ?>\" resource="../src" exclude="../src/<?php echo $additionalBundleName; ?>/{DependencyInjection,Entity,<?php echo $pluginName; ?>.php}"/>
        <?php } else { ?>
        <prototype namespace="<?php echo $namespace; ?>\" resource="../src" exclude="../src/{DependencyInjection,Entity,<?php echo $pluginName; ?>.php}"/>
        <?php } ?>
    </services>
</container>
