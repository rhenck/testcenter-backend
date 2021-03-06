<?php /** @noinspection PhpUnhandledExceptionInspection */


use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

require_once "classes/helper/FileSize.class.php";
require_once "classes/helper/Folder.class.php";
require_once "classes/files/ResourceFile.class.php";
require_once "classes/files/XMLFile.php";
require_once "classes/files/XMLFileTesttakers.php";
require_once "VfsForTest.class.php";


class WorkspaceControllerTest extends TestCase {

    private $vfs;
    private $workspaceController;

    public static function setUpBeforeClass(): void {

        VfsForTest::setUpBeforeClass();
    }

    function setUp() {

        $this->vfs = VfsForTest::setUp();
        $this->workspaceController = new WorkspaceController(1);
    }


    function tearDown() {

        unset($this->vfs);
    }


    function test___construct() {

        $workspaceDirectories = scandir(vfsStream::url('root/vo_data'));
        $expectation = array('.', '..', 'ws_1');
        $this->assertEquals($expectation, $workspaceDirectories);

        $workspace1Directories = scandir(vfsStream::url('root/vo_data/ws_1'));
        $expectation = array('.', '..', 'Booklet', 'Resource', 'SysCheck', 'Testtakers', 'Unit');
        $this->assertEquals($expectation, $workspace1Directories);
    }

    function test_getWorkspacePath() {

        $result = $this->workspaceController->getWorkspacePath();
        $expectation = 'vfs://root/vo_data/ws_1';
        $this->assertEquals($expectation, $result);
    }

    function test_getAllFiles() {

        $result = $this->workspaceController->getAllFiles();

        $this->assertEquals(9, count($result));

        $this->assertEquals('SAMPLE_BOOKLET.XML', $result[0]['filename']);
        $this->assertEquals('Booklet', $result[0]['type']);
        $this->assertArrayHasKey('filesize', $result[0]);
        $this->assertArrayHasKey('filedatetime', $result[0]);

        $this->assertEquals('SAMPLE_BOOKLET2.XML', $result[1]['filename']);
        $this->assertEquals('Booklet', $result[1]['type']);
        $this->assertArrayHasKey('filesize', $result[1]);
        $this->assertArrayHasKey('filedatetime', $result[1]);

        $this->assertEquals('trash.xml', $result[2]['filename']);
        $this->assertEquals('Booklet', $result[2]['type']);
        $this->assertArrayHasKey('filesize', $result[2]);
        $this->assertArrayHasKey('filedatetime', $result[2]);
        
        $this->assertEquals('SAMPLE_TESTTAKERS.XML', $result[3]['filename']);
        $this->assertEquals('Testtakers', $result[3]['type']);
        $this->assertArrayHasKey('filesize', $result[3]);
        $this->assertArrayHasKey('filedatetime', $result[3]);

        $this->assertEquals('trash.xml', $result[4]['filename']);
        $this->assertEquals('Testtakers', $result[4]['type']);
        $this->assertArrayHasKey('filesize', $result[4]);
        $this->assertArrayHasKey('filedatetime', $result[4]);

        $this->assertEquals('SAMPLE_SYSCHECK.XML', $result[5]['filename']);
        $this->assertEquals('SysCheck', $result[5]['type']);
        $this->assertArrayHasKey('filesize', $result[5]);
        $this->assertArrayHasKey('filedatetime', $result[5]);

        $this->assertEquals('SAMPLE_UNIT.XML', $result[6]['filename']);
        $this->assertEquals('Unit', $result[6]['type']);
        $this->assertArrayHasKey('filesize', $result[6]);
        $this->assertArrayHasKey('filedatetime', $result[6]);

        $this->assertEquals('SAMPLE_UNIT2.XML', $result[7]['filename']);
        $this->assertEquals('Unit', $result[7]['type']);
        $this->assertArrayHasKey('filesize', $result[7]);
        $this->assertArrayHasKey('filedatetime', $result[7]);

        $this->assertEquals('SAMPLE_PLAYER.HTML', $result[8]['filename']);
        $this->assertEquals('Resource', $result[8]['type']);
        $this->assertArrayHasKey('filesize', $result[8]);
        $this->assertArrayHasKey('filedatetime', $result[8]);
    }


    function test_deleteFiles() {

        $this->vfs->getChild('vo_data')->getChild('ws_1')->getChild('SysCheck')->chmod(0000);

        $result = $this->workspaceController->deleteFiles(array(
            'Resource/SAMPLE_PLAYER.HTML',
            'SysCheck/SAMPLE_SYSCHECK.XML',
            'i_dont/even.exist'
        ));

        $resources = scandir('vfs://root/vo_data/ws_1/Resource');
        $expectation = array(
            'deleted' => array('Resource/SAMPLE_PLAYER.HTML'),
            'did_not_exist' => array('i_dont/even.exist'),
            'not_allowed' => array('SysCheck/SAMPLE_SYSCHECK.XML')
        );

        $this->assertEquals($expectation, $result);
        $this->assertEquals($resources, array('.', '..'));
    }


    function test_countFiles() {

        $result = $this->workspaceController->countFiles('Testtakers');
        $this->assertEquals(2, $result);

        unlink('vfs://root/vo_data/ws_1/Testtakers/SAMPLE_TESTTAKERS.XML');

        $result = $this->workspaceController->countFiles('Testtakers');
        $this->assertEquals(1, $result);
    }


    function test_countFilesOfAllSubFolders() {

        $expectation = [
            "Testtakers" => 2,
            "SysCheck" => 1,
            "Booklet" => 3,
            "Unit" => 2,
            "Resource" => 1
        ];

        $result = $this->workspaceController->countFilesOfAllSubFolders();
        $this->assertEquals($expectation, $result);
    }
}
