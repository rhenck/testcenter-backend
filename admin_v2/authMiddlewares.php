<?php

use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;

function authWithWorkspace(Slim\Http\Request $req, Slim\Http\Response $res, $next) {

    $errorCode = 0;
    $errormessage = "Only get and post are allowed";
    if ($req->isPost() || $req->isGet()) {
        $errorCode = 401;
        $errormessage = 'Auth-Header not sufficient';
        if ($req->hasHeader('Accept')) {
            if ($req->hasHeader('AuthToken')) {
                try {
                    $authToken = json_decode($req->getHeaderLine('AuthToken'));
                    $adminToken = $authToken->at;
                    if (strlen($adminToken) > 0) {
                        $workspace = $authToken->ws;
                        if (is_numeric($workspace)) {
                            if ($workspace > 0) { // TODO 401 is not correct for missing workspace
                                $dbConnection = new DBConnectionAdmin();
                                if (!$dbConnection->isError()) {
                                    $errormessage = 'access denied';
                                    $role = $dbConnection->getWorkspaceRole($adminToken, $workspace);
                                    if (($req->isPost() && ($role == 'RW')) || ($req->isGet() && ($role != ''))) {
                                        $errorCode = 0;
                                        $_SESSION['adminToken'] = $adminToken;
                                        $_SESSION['workspace'] = $workspace;
                                        $_SESSION['workspaceDirName'] = realpath(ROOT_DIR . "/vo_data/ws_$workspace");
                                        if (!file_exists($_SESSION['workspaceDirName'])) { // TODO I moved this to auth token check - is that OK
                                            throw new HttpNotFoundException($req, "Workspace {$_SESSION['workspaceDirName']} not found");
                                        }
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

function auth(Slim\Http\Request $req, Slim\Http\Response $res, $next) {

    $errormessage = '';
    $responseStatus = 0;
    if ($req->isPost() || $req->isGet()) {
        $responseStatus = 401;
        $errormessage = 'Auth-Header not sufficient';
        if ($req->hasHeader('Accept')) {
            if ($req->hasHeader('AuthToken')) {
                try {
                    $authToken = json_decode($req->getHeaderLine('AuthToken'));
                    $adminToken = $authToken->at;
                    if (strlen($adminToken) > 0) {

                        $myDBConnection = new DBConnection();
                        if (!$myDBConnection->isError()) {
                            $errormessage = 'access denied';
                            if ($myDBConnection->isSuperAdmin($adminToken)) {
                                $responseStatus = 0;
                                $_SESSION['adminToken'] = $adminToken;
                            }
                        }
                        unset($myDBConnection);
                    }
                } catch (Exception $ex) {
                    $responseStatus = 500;
                    $errormessage = 'Something went wrong: ' . $ex->getMessage();
                }
            }
            session_write_close();
        }
    }

    if ($responseStatus === 0) {
        return $next($req, $res);
    } else {
        return $res->withStatus($responseStatus)
            ->withHeader('Content-Type', 'text/html')
            ->write($errormessage);
    }
}


/**
 * @param Request $request
 * @param Response $response
 * @param $next
 * @return mixed
 * @throws HttpForbiddenException
 * @throws HttpBadRequestException
 */
function checkWs(Request $request, Response $response, $next) {

    $route = $request->getAttribute('route');
    $params = $route->getArguments();

    if (!isset($params['ws_id']) or ((int) $params['ws_id'] < 1)) {
        throw new HttpBadRequestException($request, "No valid workspace: {$params['ws_id']}");
    }

    $adminToken = $_SESSION['adminToken'];
    error_log('token:' . $_SESSION['adminToken']);
    error_log('wsid:' . $params['ws_id']);

    $dbConnectionAdmin = new DBConnectionAdmin();

    if (!$dbConnectionAdmin->hasAdminAccessToWorkspace($adminToken, $params['ws_id'])) {
        throw new HttpForbiddenException($request,"Access to workspace ws_{$params['ws_id']} is not provided.");
    }

    return $next($request, $response);
}