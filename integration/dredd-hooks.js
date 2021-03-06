const dreddHooks = require('hooks');
const fs = require('fs');
const Multipart = require('multi-part');
const streamToString = require('stream-to-string');

const skipAfterFirstFail = false; // change this to debug
let skipTheRest = false;


const changeAuthToken = (transaction, newAuthTokenData) => {

    if (typeof transaction.request.headers['AuthToken'] === "undefined") {
        return;
    }

    let authToken = '';

    let tokenType = transaction.request.headers['AuthToken'].split(':')[0];

    switch (tokenType) {

        case 'a':
            authToken = newAuthTokenData.adminToken;
            break;
        case 'p':
            authToken = newAuthTokenData.personToken;
            break;
        case 'l':
            authToken = newAuthTokenData.loginToken;
            break;
        case 'm':
            authToken = newAuthTokenData.workspaceMonitorToken;
            break;
        case 'g':
            authToken = newAuthTokenData.groupMonitorToken;
            break;
    }

    transaction.request.headers['AuthToken'] = authToken;
};


const changeBody = (transaction, changeMap) => {

    if (!transaction.request.body) {
        return;
    }

    const body = JSON.parse(transaction.request.body);

    Object.keys(changeMap).forEach(key => {
        if (typeof body[key] !== "undefined") {
            body[key] = changeMap[key];
        }
    });

    transaction.request.body = JSON.stringify(body);
};

const changeUri = (transaction, changeMap) => {

    Object.keys(changeMap).forEach(key => {
        transaction.request.uri = transaction.request.uri.replace(key, changeMap[key]);
        transaction.fullPath = transaction.fullPath.replace(key, changeMap[key]);
    });
};


dreddHooks.beforeEachValidation(function(transaction) {

    // don't compare headers
    transaction.real.headers = {};
    transaction.expected.headers = {};
});


dreddHooks.beforeEach(function(transaction, done) {

    // skip everything after first failed test
    if (skipTheRest && skipAfterFirstFail) {
        transaction.skip = true;
        return done();
    }

    // use virtual environment
    transaction.request.headers['TestMode'] = true;

    // inject login credentials if necessary
    switch (transaction.expected.statusCode) {
        case '200':
        case '201':
        case '207':
        case '422':
            changeAuthToken(transaction, {
                adminToken: 'static:admin:super',
                loginToken: 'static:login:sample_user',
                personToken: 'static:person:sample_group_sample_user_xxx',
                workspaceMonitorToken: 'static:person:sample_group_test-study-monitor_',
                groupMonitorToken: 'static:person:sample_group_test-group-monitor_'
            });
            break;
        case '400':
            changeBody(transaction, {
                password: '__totally_invalid_password__',
                code: "__invalid_code__"
            });
            changeAuthToken(transaction, {
                loginToken: 'static:login:sample_user',
                adminToken: 'static:admin:super',
            });
            break;
        case '401':
            changeAuthToken(transaction,{});
            break;
        case '403':
            changeAuthToken(transaction,{
                adminToken: '__invalid_token__',
                loginToken: '__invalid_token__',
                personToken: '__invalid_token__',
                workspaceMonitorToken: 'static:person:sample_group_sample_user_xxx',
                groupMonitorToken: 'static:person:sample_group_sample_user_xxx'
            });
            break;
        case '404':
            changeAuthToken(transaction, {
                adminToken: 'static:admin:super',
                loginToken: 'static:login:sample_user',
                personToken: 'static:person:sample_group_sample_user_xxx',
                workspaceMonitorToken: 'static:person:sample_group_test-study-monitor_',
                groupMonitorToken: 'static:person:sample_group_test-group-monitor_'
            });
            changeUri(transaction, {
                '/workspace/1': '/workspace/13',
                '/group/sample_group': '/group/invalid_group'
            });
            break;
        case '410':
            changeAuthToken(transaction,{
                adminToken: 'static:admin:expired_user',
                loginToken: 'static:login:expired_user',
                personToken: 'static:person:expired_group_expired_user_xxx',
                workspaceMonitorToken: 'static:person:expired_group_expired-study-monitor_',
                groupMonitorToken: 'static:person:expired_group_expired-group-monitor_'
            });
            break;
        default:
            transaction.skip = true;
            return done();
    }

    done();
});

dreddHooks.afterEach(function(transaction, done) {

    // die after first failure
    if (transaction.results.valid === false) {
        skipTheRest = true;
    }

    done();
});


dreddHooks.before('specs > /workspace/{ws_id}/file > upload file > 201 > application/json', async function(transaction, done) {

    const form = new Multipart();

    form.append('fileforvo', fs.createReadStream('../sampledata/Unit.xml', 'utf-8'));

    transaction.request.body = await streamToString(form.stream());
    transaction.request.headers['Content-Type'] = form.getHeaders()['content-type'];
    done();
});


dreddHooks.before('specs > /workspace/{ws_id}/file > upload file > 422', async function(transaction, done) {

    const form = new Multipart();

    form.append('fileforvo', fs.createReadStream('../sampledata/Unit.xml', 'utf-8'));

    transaction.request.body = await streamToString(form.stream());
    transaction.request.body = transaction.request.body
        .replace('<Unit', '<Invalid')
        .replace('</Unit', '</Invalid');
    transaction.request.headers['Content-Type'] = form.getHeaders()['content-type'];
    done();
});


dreddHooks.beforeValidation('specs > /test/{test_id}/resource/{resource_name} > get resource by name > 200 > text/plain', function(transaction, done) {

    transaction.expected.body = fs.readFileSync('../sampledata/Player.html').toString();
    done();
});

dreddHooks.beforeValidation('specs > /booklet/{booklet_name} > get a booklet > 200 > application/xml', function(transaction, done) {

    transaction.real.body = '';
    transaction.expected.body = '';
    done();
});
