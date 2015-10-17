<?php
namespace Codeception\Lib\Generator;

use Codeception\Lib\Generator\Shared\Classname;
use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;

class WPUnit {
    use Classname;
    use Namespaces;

    protected $template  = <<<EOF
<?php
{{namespace}}
class {{name}}Test extends \WP_UnitTestCase
{

    protected \$backupGlobals = false;
    
    public function setUp()
    {
        // before
        parent::setUp();

        // your set up methods here
    }

    public function tearDown()
    {
        // your tear down methods here

        // then
        parent::tearDown();
    }

    // tests
    public function testMe()
    {
    }

}
EOF;

    protected $settings;
    protected $name;

    public function __construct($settings, $name)
    {
        $this->settings = $settings;
        $this->name = $this->removeSuffix($name, 'Test');
    }

    public function produce()
    {
        $ns = $this->getNamespaceString($this->settings['namespace'].'\\'.$this->name);

        return (new Template($this->template))
            ->place('namespace', $ns)
            ->place('name', $this->getShortClassName($this->name))
            ->produce();
    }

}

