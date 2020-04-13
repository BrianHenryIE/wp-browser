<?php namespace tad\WPBrowser;

use Codeception\Exception\ConfigurationException;
use Codeception\Lib\ModuleContainer;

class wpbrowserTest extends \Codeception\Test\Unit
{
    /**
     * It should throw if a module requirement is not satisfied
     *
     * @test
     */
    public function should_throw_if_a_module_requirement_is_not_satisfied()
    {
        $this->expectException(ConfigurationException::class);

        requireCodeceptionModules('TestModule', ['NotExisting']);
    }


    /**
     * It should throw if one of required modules is not present
     *
     * @test
     */
    public function should_throw_if_one_of_required_modules_is_not_present()
    {
        $this->expectException(ConfigurationException::class);

        requireCodeceptionModules('TestModule', ['NotExisting', 'Filesystem']);
    }

    /**
     * It should throw message with information about all missing requirements
     *
     * @test
     */
    public function should_throw_message_with_information_about_all_missing_requirements()
    {
        ModuleContainer::$packages['ModuleOne'] = 'lucatume/module-one';
        ModuleContainer::$packages['ModuleTwo'] = 'lucatume/module-two';

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessageRegExp('/.*ModuleOne.*ModuleTwo.*lucatume\\/module-one.*lucatume\\/module-two/us');

        requireCodeceptionModules('TestModule', [ 'ModuleOne', 'Filesystem', 'ModuleTwo' ]);
    }

    /**
     * It should not throw if module requirements are met
     *
     * @test
     */
    public function should_not_throw_if_module_requirements_are_met()
    {
        requireCodeceptionModules('TestModule', [ 'Db', 'Filesystem' ]);
    }
}
