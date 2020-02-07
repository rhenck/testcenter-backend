<?php

use Slim\Exception\HttpNotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;

class NormalAuthWithWorkspaceInHeader {

    function __invoke(Request $req, Response $res, $next) {

        $errorCode = 0;
        $errormessage = "Only get and post are allowed";
        if ($req->isPost() || $req->isGet()) {
            $errorCode = 401;
            $errormessage = 'Auth-Header not sufficient';
            if ($req->hasHeader('Accept')) {
                if ($req->hasHeader('AuthToken')) {
                    try {
                        $authToken = JSON::decode($req->getHeaderLine('AuthToken'));
                        $adminToken = $authToken->at;
                        if (strlen($adminToken) > 0) {
                            $workspaceId = $authToken->ws;
                            if (is_numeric($workspaceId)) {
                                if ($workspaceId > 0) { // TODO 401 is not correct for missing workspaceId
                                    $dbConnection = new DBConnectionAdmin();
                                    $role = $dbConnection->getWorkspaceRole($adminToken, $workspaceId);
                                    $errormessage = 'access denied for ws_' . $workspaceId . ' as ' . print_r($role,1);
                                    if (($req->isPost() && ($role == 'RW')) || ($req->isGet() && ($role != ''))) {
                                        $errorCode = 0;
                                        $_SESSION['adminToken'] = $adminToken;
                                        $_SESSION['workspace'] = $workspaceId;
                                        $_SESSION['workspaceDirName'] = realpath(ROOT_DIR . "/vo_data/ws_$workspaceId");
                                        if (!file_exists($_SESSION['workspaceDirName'])) { // TODO I moved this to auth token check - is that OK
                                            throw new HttpNotFoundException($req, "Workspace {$_SESSION['workspaceDirName']} not found");
                                        }
                                    }

                                }
                            }
                        }
                    } catch (Exception $ex) {
                        $errorCode = 500;
                        $errormessage = 'Something went wrong: ' . $ex->getMessage();
                    }
                }
                session_write_close();
            }
        }

        if ($errorCode === 0) {
            return $next($req, $res);
        } else {
            return $res->withStatus($errorCode)
                ->withHeader('Content-Type', 'text/html')
                ->write($errormessage);
        }
    }
}