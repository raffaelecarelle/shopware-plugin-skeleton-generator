<?php echo '<?'; ?>xml version="1.0" ?>

<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <services>
        <defaults autowire="true" autoconfigure="true" public="false"/>

        <prototype namespace="<?php echo $namespace; ?>\" resource="../" exclude="../{DependencyInjection,Entity,<?php echo $pluginName; ?>.php}"/>
    </services>
</container>
