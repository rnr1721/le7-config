<?php

declare(strict_types=1);

use Core\Config\ConfigFactoryGeneric;
use Core\Interfaces\ConfigFactoryInterface;
use Core\Interfaces\ConfigInterface;
use Core\Cache\SCFactoryGeneric;
use Psr\SimpleCache\CacheInterface;

require_once 'vendor/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';

class ConfigTest extends PHPUnit\Framework\TestCase
{

    private CacheInterface $cache;
    private ConfigFactoryInterface $configFactory;
    private ConfigFactoryInterface $configFactoryCache;

    protected function setUp(): void
    {
        $this->configFactory = new ConfigFactoryGeneric();

        $cacheFactory = new SCFactoryGeneric();

        $cfolder = getcwd() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'cache';

        if (!file_exists($cfolder)) {
            mkdir($cfolder, 0777, true);
        }

        $this->cache = $cacheFactory->getFileCache($cfolder);

        $this->configFactoryCache = new ConfigFactoryGeneric($this->cache);
    }

    public function testArray()
    {
        $filename = $this->getConfig('config.php');

        $array = require $filename;

        $config = $this->configFactory->fromArray($array);

        $startsWithChild = count($config->arrayWithKeyStartWith('doctrine','many_elements'));

        $this->assertEquals($startsWithChild, 3);
        
        $startsWithParent = count($config->arrayWithKeyStartWith('laravel'));
        
        $this->assertEquals($startsWithParent, 2);
        
        $this->makeTest($config);
    }

    public function testArrayFile()
    {

        $filename = $this->getConfig('config.php');

        $config = $this->configFactory->fromArrayFile($filename, 'Custom Array Config');

        $this->makeTest($config);
    }

    public function testArrayFileCache()
    {

        $filename = $this->getConfig('config.php');

        $config = $this->configFactoryCache->fromArrayFile($filename, 'Custom Array Config', 'arrayFile');

        $this->makeTest($config);
        $this->makeTest($config);

        $this->cache->clear();
    }

    public function testJson()
    {
        $jsonFile = $this->getConfig('config.json');

        $config = $this->configFactory->fromJsonFile($jsonFile, 'Custom JSON file');

        $this->makeTest($config);
    }

    public function testJsonCache()
    {
        $jsonFile = $this->getConfig('config.json');

        $config = $this->configFactoryCache->fromJsonFile($jsonFile, 'Custom JSON file', 'jsonFile');

        $this->makeTest($config);
        $this->makeTest($config);

        $this->cache->clear();
    }

    public function testIni()
    {
        $iniFile = $this->getConfig('config.ini');

        $config = $this->configFactory->fromIniFile($iniFile, 'Custom INI file');

        $this->makeTest($config);
    }

    public function testIniCache()
    {
        $iniFile = $this->getConfig('config.ini');

        $config = $this->configFactoryCache->fromIniFile($iniFile, 'Custom INI file', 'iniFile');

        $this->makeTest($config);
        $this->makeTest($config);

        $this->cache->clear();
    }

    public function testHarvest()
    {

        $ds = DIRECTORY_SEPARATOR;

        $dirs = [
            getcwd() . $ds . 'tests' . $ds . 'configDir',
            getcwd() . $ds . 'tests' . $ds . 'configDir2'
        ];

        $config = $this->configFactory->harvest($dirs, 'Config');

        $this->makeHarvestTest($config);
    }

    public function testHarvestCache()
    {

        $ds = DIRECTORY_SEPARATOR;

        $dirs = [
            getcwd() . $ds . 'tests' . $ds . 'configDir',
            getcwd() . $ds . 'tests' . $ds . 'configDir2'
        ];

        $config = $this->configFactoryCache->harvest($dirs, 'Config', 'foldersConfig');

        $this->makeHarvestTest($config);
    }

    public function testHarvestCache2()
    {

        $ds = DIRECTORY_SEPARATOR;

        $dirs = [
            getcwd() . $ds . 'tests' . $ds . 'configDir',
            getcwd() . $ds . 'tests' . $ds . 'configDir2'
        ];

        $config = $this->configFactoryCache->harvest($dirs, 'Config', 'foldersConfig');

        $this->makeHarvestTest($config);
        $this->cache->clear();
    }

    public function makeHarvestTest(ConfigInterface $config)
    {
        $this->assertEquals($config->int('param1'), 0);
        $this->assertEquals($config->int('param2'), 1);
        $this->assertEquals($config->string('param3'), 'test');
        $this->assertIsArray($config->array('general'));
        $this->assertEquals($config->string('general.myparam3'), 45);
        $this->assertEquals($config->float('general.test4'), 4.44);
        $this->assertEquals($config->string('name'), 'rnr1721');
        $config->applyFilter('123', 'test1');
        $config->applyFilter('testvar', 'testvar1');
        $this->assertEquals($config->stringf('param777'), 'Hellotest1World');
        $this->assertEquals($config->stringf('param888.testparam1'), 'onetestvar1two');
        $this->assertEquals($config->stringf('section.myparamOld.another'), 'twotestvar1one');
        // Non existing param
        $this->assertEquals($config->stringf('section.myparamOld.anothervvv', '333'), '333');

        // Register param with filter
        $config->registerParam('section.myparamnew', 'test2', 'mpn');
        $config->registerParam('section.myparamnew2', '{mpn}/777');
        $this->assertEquals($config->stringf('section.myparamnew2'), 'test2/777');
        
    }

    public function makeTest(ConfigInterface $config)
    {
        // When exists
        $this->assertEquals($config->int('one'), '12');
        $this->assertEquals($config->string('two'), '2');
        $this->assertEquals($config->bool('three'), false);
        $this->assertIsArray($config->array('four'));
        $this->assertEquals($config->string('four.four_one'), '332');
        $this->assertEquals($config->string('four.four_two'), '3332ddd');
        $this->assertEquals($config->bool('four.four_three'), true);
        $this->assertNull($config->path('four.four_four'));
        $this->assertIsArray($config->array('four.four_six'));
        $this->assertEquals($config->float('four.four_six.three'), 65.33);

        // When defaults
        $this->assertEquals($config->int('one', 12), '12');
        $this->assertEquals($config->string('two', '2'), '2');
        $this->assertEquals($config->bool('three', false), false);
        $this->assertIsArray($config->array('four', ['one', 'two']));
        $this->assertEquals($config->string('four.four_one', '332'), '332');
        $this->assertEquals($config->string('four.four_two', '3deeee'), '3332ddd');
        $this->assertEquals($config->bool('four.four_three', true), true);
        $this->assertNull($config->path('four.four_four', null));
        $this->assertIsArray($config->array('four.four_six'));
        $this->assertEquals($config->float('four.four_six.three'), 65.33);
        $this->assertEquals($config->int('non_exists', 30), 30);

        // As array
        $this->assertEquals($config['one'], 12);
        $this->assertEquals($config['two'], '2');
        $this->assertEquals($config['three'], false);
        $this->assertIsArray($config['four']);
        $this->assertEquals($config['four']['four_one'], '332');
        $this->assertEquals($config['four']['four_two'], '3332ddd');
        $this->assertNull($config['four4444']);
        $this->assertIsArray($config['four']['four_six']);
        $this->assertEquals($config['four']['four_six']['three'], 65.33);

        if (!$config->path('five')) {
            $config->registerParam('five', "test");
        }

        if (!$config->path('four.new3')) {
            $config->registerParam('four.new3', 31);
        }

        $this->assertEquals($config->int('four.new3'), 31);
        $this->assertEquals($config->string('five'), "test");
    }
    
    public function getConfig(string $config)
    {
        $ds = DIRECTORY_SEPARATOR;
        $path = getcwd() . $ds . 'tests' . $ds . 'config' . $ds;
        return $path . $config;
    }

}
