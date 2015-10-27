<?php

namespace ExampleModule;

class Module
{
    public function getServiceConfig()
    {
        return array(
            'services' => array(
                'exampleservice' => 'this string is managed as "service" in service manager',
            )
        );
    }
}
