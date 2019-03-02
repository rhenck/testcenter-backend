<?php
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

session_start();
require '../vendor/autoload.php';
$app = new \Slim\App();
$app->add(function (ServerRequestInterface $req, ResponseInterface $res, $next) {
    if ($req->hasHeader('Accept')) {
        $personToken = '';
        $loginToken = '';
        $bookletDbId = 0;
        if ($req->hasHeader('AuthToken')) {
            $authToken = json_decode($req->getHeaderLine('AuthToken'));
            $personToken = $authToken->p;
            $loginToken = $authToken->l;
            $bookletDbId = $authToken->b;
        }

        $_SESSION['loginToken'] = $loginToken;
        $_SESSION['personToken'] = $personToken;
        $_SESSION['bookletDbId'] = $bookletDbId;

        session_write_close();
    }

    return $next($req, $res);
});

// HELPERs #######################################################
function jsonencode($obj)
{
    return json_encode($obj, JSON_UNESCAPED_UNICODE);
}

// global Variables #############################################
$container = $app->getContainer();
$container['code_directory'] = __DIR__.'/../vo_code';
$container['data_directory'] = __DIR__.'/../vo_data';
// use in Routes: $directory = $this->get('data_directory');


// ##############################################################
// ######                    routes                        ######
// ##############################################################
$app->post('/login', function (ServerRequestInterface $request, ResponseInterface $response) {
    try {
        $myerrorcode = 500;
        $bodydata = json_decode($request->getBody());
		$loginName = isset($bodydata->n) ? $bodydata->n : '';
        $loginPassword = isset($bodydata->p) ? $bodydata->p : '';
        // do not take the AuthHeader-Data!
        $loginToken = isset($bodydata->lt) ? $bodydata->lt : '';
        $personToken = isset($bodydata->pt) ? $bodydata->pt : '';

        $myreturn = [
            'logintoken' => '',
            'persontoken' => '',
            'mode' => '',
            'groupname' => '',
            'loginname' => '',
            'workspaceName' => '',
            'booklets' => [],
            'code' => '',
            'booklet' => 0
        ];

        require_once($this->get('code_directory') . '/DBConnectionStart.php');
        $myDBConnection = new DBConnectionStart();
        if (!$myDBConnection->isError()) {
            $myerrorcode = 401;
    
            // //////////////////////////////////////////////////////////////////////////////////////////////////
            // CASE A: login by name and password ///////////////////////////////////////////////////////////////
            if (strlen($loginName) > 0 && strlen($loginPassword) > 0) {

                $workspaceDir = opendir($this->get('data_directory'));
                $testeefiledirprefix = $this->get('data_directory') . '/ws_';
                $myWorkspace = 0;
                if ($workspaceDir) {

                    require_once($this->get('code_directory') . '/XMLFileTesttakers.php'); // // // // ========================

                    $hasfound = false;
                    while (($subdir = readdir($workspaceDir)) !== false) {
                        $mysplits = explode('_', $subdir);

                        if (count($mysplits) == 2) {
                            if (($mysplits[0] == 'ws') && is_numeric($mysplits[1])) {
                                $TesttakersDirname = $testeefiledirprefix . $mysplits[1] . '/Testtakers';
                                if (file_exists($TesttakersDirname)) {
                                    $mydir = opendir($TesttakersDirname);
                                    while (($entry = readdir($mydir)) !== false) {
                                        $fullfilename = $TesttakersDirname . '/' . $entry;
                                        if (is_file($fullfilename) && (strtoupper(substr($entry, -4)) == '.XML')) {
                                            // &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
                                            $xFile = new XMLFileTesttakers($fullfilename);

                                            if ($xFile->isValid()) {
                                                if ($xFile->getRoottagName()  == 'Testtakers') {
                                                    $myBooklets = $xFile->getLoginData($loginName, $loginPassword);
                                                    if (count($myBooklets['booklets']) > 0) {
                                                        $myWorkspace = $mysplits[1];
                                                        $hasfound = true;
                                                        break;
                                                    }
                                                }
                                            }
                                            // &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
                                        }
                                    } // lesen von Verzeichnisinhalt Testtakers
                                }
                            }
                        }
                        if ($hasfound) {
                            break;
                        }
                    } // lesen subdirs von vo_data


                    if (strlen($myWorkspace) > 0) {
                        $loginreturn = $myDBConnection->login(
                            $myWorkspace, $myBooklets['groupname'], $myBooklets['loginname'], $myBooklets['mode'], $myBooklets['booklets']);
                        if (strlen($loginreturn) > 0) {
                            $myerrorcode = 0;
                            $myreturn = [
                                'logintoken' => $loginreturn,
                                'persontoken' => '',
                                'mode' => $myBooklets['mode'],
                                'groupname' => $myBooklets['groupname'],
                                'loginname' => $myBooklets['loginname'],
                                'workspaceName' => $myDBConnection->getWorkspaceName($myWorkspace),
                                'booklets' => $myBooklets['booklets'],
                                'code' => '',
                                'booklet' => 0
                            ];
                        }
                    }
                }

            // //////////////////////////////////////////////////////////////////////////////////////////////////
            // CASE B: get logindata by persontoken //////////////////////////////////////////////////////////////
            } elseif (strlen($personToken) > 0) {
                $dbReturn = $myDBConnection->getAllBookletsByPersonToken($personToken);
                if (count($dbReturn['booklets']) > 0 ) {
                    $myerrorcode = 0;
                    $myreturn = $dbReturn;
                    $myreturn['persontoken'] = $personToken;
                    $myreturn['booklet'] = 0;

                    // $bookletfolder = $this->get('data_directory') . '/ws_' . $dbReturn['ws'] . '/Booklet';
    
                    // if (file_exists($bookletfolder)) {
                    //     $mydir = opendir($bookletfolder);
                    //     $bookletlist = [];
    
                    //     require_once($this->get('code_directory') . '/XMLFile.php'); // // // // ========================
                    //     while (($entry = readdir($mydir)) !== false) {
                    //         $fullfilename = $bookletfolder . '/' . $entry;
                    //         if (is_file($fullfilename) && (strtoupper(substr($entry, -4)) == '.XML')) {
    
                    //             $xFile = new XMLFile($fullfilename);
                    //             if ($xFile->isValid()) {
                    //                 $bKey = $xFile->getId();
                    //                 $bookletlist[$bKey] = [
                    //                         'label' => $xFile->getLabel(),
                    //                         'filename' => $entry];
                    //             }
                    //         }
                    //     }
                    //     $myerrorcode = 0;
                        
    
                    //     // transform bookletid[] to bookletdata[]
                    //     $newBookletList = [];
                    //     foreach($dbReturn['booklets'] as $code => $booklets) {
                    //         $newBooklets = [];
                    //         foreach($booklets as $bookletid) {
                    //             $newBooklet['id'] = $bookletid;
                    //             if ((count($bookletlist) > 0) and isset($bookletlist[$bookletid])) {
                    //                 $bData = $bookletlist[$bookletid];
                    //                 $newBooklet['filename'] = $bData['filename'];
                    //                 $newBooklet['label'] = $bData['label'];
                    //             }
                    //             array_push($newBooklets, $newBooklet);
                    //         }
                    //         $newBookletList[$code] = $newBooklets;
                    //     }

                    //     $myreturn = [
                    //         'logintoken' => $dbReturn['$logintoken'],
                    //         'persontoken' => $personToken,
                    //         'mode' => $dbReturn['mode'],
                    //         'groupname' => $dbReturn['groupname'],
                    //         'loginname' => $dbReturn['loginname'],
                    //         'workspaceName' => $dbReturn['workspaceName'],
                    //         'booklets' => $newBookletList,
                    //         'code' => $dbReturn['code'],
                    //         'booklet' => 0
                    //     ];
                    // }
                }
            // //////////////////////////////////////////////////////////////////////////////////////////////////
            // CASE C: get logindata by logintoken //////////////////////////////////////////////////////////////
            } elseif (strlen($loginToken) > 0) {
                $dbReturn = $myDBConnection->getAllBookletsByLoginToken($loginToken);
                if (count($dbReturn['booklets']) > 0 ) {
                    $myerrorcode = 0;
                    $myreturn = $dbReturn;
                    $myreturn['persontoken'] = $personToken;
                    $myreturn['booklet'] = 0;
                    $myreturn['code'] = '';

                    // $bookletfolder = $this->get('data_directory') . '/ws_' . $dbReturn['ws'] . '/Booklet';
    
                    // if (file_exists($bookletfolder)) {
                    //     $mydir = opendir($bookletfolder);
                    //     $bookletlist = [];
    
                    //     require_once($this->get('code_directory') . '/XMLFile.php'); // // // // ========================
                    //     while (($entry = readdir($mydir)) !== false) {
                    //         $fullfilename = $bookletfolder . '/' . $entry;
                    //         if (is_file($fullfilename) && (strtoupper(substr($entry, -4)) == '.XML')) {
    
                    //             $xFile = new XMLFile($fullfilename);
                    //             if ($xFile->isValid()) {
                    //                 $bKey = $xFile->getId();
                    //                 $bookletlist[$bKey] = [
                    //                         'label' => $xFile->getLabel(),
                    //                         'filename' => $entry];
                    //             }
                    //         }
                    //     }
                    //     $myerrorcode = 0;
                        
                    //     // transform bookletid[] to bookletdata[]
                    //     $newBookletList = [];
                    //     foreach($dbReturn['booklets'] as $code => $booklets) {
                    //         $newBooklets = [];
                    //         foreach($booklets as $bookletid) {
                    //             $newBooklet['id'] = $bookletid;
                    //             if ((count($bookletlist) > 0) and isset($bookletlist[$bookletid])) {
                    //                 $bData = $bookletlist[$bookletid];
                    //                 $newBooklet['filename'] = $bData['filename'];
                    //                 $newBooklet['label'] = $bData['label'];
                    //             }
                    //             array_push($newBooklets, $newBooklet);
                    //         }
                    //         $newBookletList[$code] = $newBooklets;
                    //     }
                    //     $myreturn = [
                    //         'logintoken' => $dbReturn['$logintoken'],
                    //         'persontoken' => '',
                    //         'mode' => $dbReturn['mode'],
                    //         'groupname' => $dbReturn['groupname'],
                    //         'loginname' => $dbReturn['loginname'],
                    //         'workspaceName' => $dbReturn['workspaceName'],
                    //         'booklets' => $newBookletList,
                    //         'code' => '',
                    //         'booklet' => 0
                    //     ];
                    // }
                }
            }
        }
        unset($myDBConnection);

        if ($myerrorcode == 0) {
            $responseData = jsonencode($myreturn);
            $response->getBody()->write($responseData);
    
            $responseToReturn = $response->withHeader('Content-type', 'application/json;charset=UTF-8');
        } else {
            $responseToReturn = $response->withStatus($myerrorcode)
                ->withHeader('Content-Type', 'text/html')
                ->write('Something went wrong!');
        }

        return $responseToReturn;
    } catch (Exception $ex) {
        return $response->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write('Something went wrong: ' . $ex->getMessage());
    }
});


// ##############################################################
// ##############################################################
$app->get('/bookletstatus', function (ServerRequestInterface $request, ResponseInterface $response) {
    try {
        $myerrorcode = 503;
        $loginToken = $_SESSION['loginToken'];
        $personToken = $_SESSION['personToken'];
        $bookletDbId = $_SESSION['bookletDbId'];

        $code = $request->getQueryParam('c', '');
        $bookletid = $request->getQueryParam('b', '');

        $myreturn = '';

        require_once($this->get('code_directory') . '/DBConnectionStart.php');
        $myDBConnection = new DBConnectionStart();
        if (!$myDBConnection->isError()) {
            $myerrorcode = 401;
            $myreturn = $myDBConnection->getBookletStatus($loginToken, $code, $personToken, $bookletid, $bookletDbId);
            if ($myreturn !== []) {
                $myerrorcode = 0;
                if (strlen($myreturn['label']) === 0) {
                    // booklet not found in database, so look at xml
                    require_once($this->get('code_directory') . '/FilesFactory.php');
                    $myreturn['label'] = XFileFactory::getBookletName(
                        $myDBConnection->getWorkspaceId($loginToken), $bookletid);
                }
            }
        }
        unset($myDBConnection);

        if ($myerrorcode == 0) {
            $responseData = jsonencode($myreturn);
            $response->getBody()->write($responseData);

            $responseToReturn = $response->withHeader('Content-type', 'application/json;charset=UTF-8');
        } else {
            $responseToReturn = $response->withStatus($myerrorcode)
                ->withHeader('Content-Type', 'text/html')
                ->write('Something went wrong!');
        }

        return $responseToReturn;
    } catch (Exception $ex) {
        return $response->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write('Something went wrong: ' . $ex->getMessage());
    }
});

$app->run();
?>