<!-- ES2015/ES6 modules polyfill -->
<script type="module">
    window._spice_has_module_support = true;
</script>
<script>
    window.addEventListener("load", function() {
        if (window._spice_has_module_support) return;
        var loader = document.createElement("script");
        loader.src = "/assets/spiceHtml5/src/thirdparty/browser-es-module-loader/dist/browser-es-module-loader.js";
        document.head.appendChild(loader);
    });
</script>

<style>
.spice-screen
{
    min-height: 600px;
    height: 100%;
    margin: 10px;
    padding: 0;
}
</style>

<script type="module" crossorigin="anonymous">
    import * as SpiceHtml5 from './assets/spiceHtml5/src/main.js';

    var host = null, port = null;
    var sc;

    function spice_set_cookie(name, value, days) {
        var date, expires;
        date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toGMTString();
        document.cookie = name + "=" + value + expires + "; path=/";
    };

    function spice_query_var(name, defvalue) {
        var match = RegExp('[?&]' + name + '=([^&]*)')
                          .exec(window.location.search);
        return match ?
            decodeURIComponent(match[1].replace(/\+/g, ' '))
            : defvalue;
    }

    function spice_error(e)
    {
        disconnect();
        if (e !== undefined && e.message === "Permission denied.") {
          var pass = prompt("Password");
          connect(pass);
        }
    }

    function connectToTerminal(uri, hostId, project, instance, password = undefined)
    {
        var host, port, scheme = "ws://";

        // By default, use the host and port of server that served this file
        host = spice_query_var('host', window.location.hostname);

        // Note that using the web server port only makes sense
        //  if your web server has a reverse proxy to relay the WebSocket
        //  traffic to the correct destination port.
        var default_port = window.location.port;
        if (!default_port) {
            if (window.location.protocol == 'http:') {
                default_port = 80;
            }
            else if (window.location.protocol == 'https:') {
                default_port = 443;
            }
        }
        port = spice_query_var('port', default_port);
        if (window.location.protocol == 'https:') {
            scheme = "wss://";
        }

        // If a token variable is passed in, set the parameter in a cookie.
        // This is used by nova-spiceproxy.
        var token = spice_query_var('token', null);
        if (token) {
            spice_set_cookie('token', token, 1)
        }

        if (password === undefined) {
            password = spice_query_var('password', '');
        }
        var path = spice_query_var('path', '/node/terminal');

        if ((!host) || (!port)) {
            console.log("must specify host and port in URL");
            return;
        }

        if (sc) {
            sc.stop();
        }

        uri = scheme + host + ":" + port;

        if (path) {
          uri += path[0] == '/' ? path : ('/' + path);
        }

        uri = `${uri}/?ws_token=${userDetails.apiToken}&user_id=${userDetails.userId}&hostId=${hostId}&project=${project}&instance=${instance}`

        try
        {
            sc = new SpiceHtml5.SpiceMainConn({uri: uri, screen_id: "spice-screen", password: password, onerror: spice_error, onagent: agent_connected });
        }
        catch (e)
        {
            alert(e.toString());
            disconnect();
        }

    }

    function disconnect()
    {
        console.log(">> disconnect");
        if (sc) {
            sc.stop();
        }
        if (window.File && window.FileReader && window.FileList && window.Blob)
        {
            var spice_xfer_area = document.getElementById('spice-xfer-area');
            if (spice_xfer_area != null) {
              document.getElementById('spice-area').removeChild(spice_xfer_area);
            }
            document.getElementById('spice-area').removeEventListener('dragover', SpiceHtml5.handle_file_dragover, false);
            document.getElementById('spice-area').removeEventListener('drop', SpiceHtml5.handle_file_drop, false);
        }
        console.log("<< disconnect");
    }

    function agent_connected(sc)
    {
        window.addEventListener('resize', SpiceHtml5.handle_resize);
        window.spice_connection = this;

        SpiceHtml5.resize_helper(this);

        if (window.File && window.FileReader && window.FileList && window.Blob)
        {
            var spice_xfer_area = document.createElement("div");
            spice_xfer_area.setAttribute('id', 'spice-xfer-area');
            document.getElementById('spice-area').appendChild(spice_xfer_area);
            document.getElementById('spice-area').addEventListener('dragover', SpiceHtml5.handle_file_dragover, false);
            document.getElementById('spice-area').addEventListener('drop', SpiceHtml5.handle_file_drop, false);
        }
        else
        {
            console.log("File API is not supported");
        }
    }

    window.disconnectFromTerminal = disconnect
    window.connectToTerminal = connectToTerminal
</script>

<div id="containerBox" class="boxSlide">
    <div class="row border-bottom mb-2">
    <div class="col-md-12 text-center">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2">
              <div class="btn-toolbar float-right">
                <div class="btn-group mr-2">
                    <button data-toggle="tooltip" data-placement="bottom" title="Start Instance" class="btn btn-sm btn-success changeInstanceState" data-action="start">
                        <i class="fas fa-play"></i>
                    </button>
                    <button data-toggle="tooltip" data-placement="bottom" title="Stop Instance" class="btn btn-sm btn-danger changeInstanceState" data-action="stop">
                        <i class="fas fa-stop"></i>
                    </button>
                    <button data-toggle="tooltip" data-placement="bottom" title="Restart Instance" class="btn btn-sm btn-warning changeInstanceState" data-action="restart">
                        <i class="fa fa-sync"></i>
                    </button>
                    <hr/>
                    <button data-toggle="tooltip" data-placement="bottom" title="Freeze Instance" class="btn btn-sm btn-info changeInstanceState" data-action="freeze">
                        <i class="fas fa-snowflake"></i>
                    </button>
                    <button data-toggle="tooltip" data-placement="bottom" title="Unfreeze Instance" class="btn btn-sm btn-primary changeInstanceState" data-action="unfreeze">
                        <i class="fas fa-mug-hot"></i>
                    </button>
                </div>
              </div>

            <h4 class="pt-1"> <u>
                <span id="container-currentState"></span>
                <span id="container-containerNameDisplay"></span>
            </u></h4>
            <div class="btn-toolbar float-right">
              <div class="btn-group mr-2">
                  <button data-toggle="tooltip" data-placement="bottom" title="Attach Volume" class="btn btn-sm btn-success" id="attachVolumesBtn">
                      <i class="fas fa-hdd"></i>
                  </button>
                  <button data-toggle="tooltip" data-placement="bottom" title="Assign Profiles" class="btn btn-sm btn-purple" id="assignProfilesBtn">
                      <i class="fas fa-users"></i>
                  </button>
                  <button data-toggle="tooltip" data-placement="bottom" title="Create Image" class="btn btn-sm btn-secondary" id="craeteImage">
                      <i class="fas fa-image"></i>
                  </button>
                  <button data-toggle="tooltip" data-placement="bottom" title="Settings" class="btn btn-sm btn-primary editContainerSettings">
                      <i class="fas fa-cog"></i>
                  </button>
                  <button data-toggle="tooltip" data-placement="bottom" title="Snapshot" class="btn btn-sm btn-success takeSnapshot">
                      <i class="fas fa-camera"></i>
                  </button>
                  <hr/>
                  <button data-toggle="tooltip" data-placement="bottom" title="Copy Instance" class="btn btn-sm btn-info copyContainer">
                      <i class="fas fa-copy"></i>
                  </button>
                  <button data-toggle="tooltip" data-placement="bottom" title="Migrate Instance" class="btn btn-sm btn-primary migrateContainer">
                      <i class="fas fa-people-carry"></i>
                  </button>
                  <button data-toggle="tooltip" data-placement="bottom" title="Rename Instance" class="btn btn-sm btn-warning renameContainer">
                      <i class="fas fa-edit"></i>
                  </button>
                  <button data-toggle="tooltip" data-placement="bottom" title="Delete" class="btn btn-sm btn-danger deleteContainer">
                      <i class="fas fa-trash"></i>
                  </button>
              </div>
            </div>
        </div>
    </div>
    </div>
    <div class="row border-bottom mb-2 pb-2" id="containerViewBtns">
        <div class="col-md-12 text-center justify-content">
            <button type="button" class="btn text-white btn-outline-primary active" id="goToDetails">
                <i class="fas fa-info pr-2"></i>Details
            </button>
            <button type="button" class="btn text-white btn-outline-primary" id="goToConsole">
                <i class="fas fa-terminal pr-2"></i>Console
            </button>
            <button type="button" class="btn text-white btn-outline-primary" id="goToTerminal">
                <i class="fas fa-tv pr-2"></i>Terminal
            </button>
            <button type="button" class="btn text-white btn-outline-primary" id="goToBackups">
                <i class="fas fa-save pr-2"></i>Backups
            </button>
            <button type="button" class="btn text-white btn-outline-primary" id="goToFiles">
                <i class="fas fa-save pr-2"></i>Files
            </button>
            <button type="button" class="btn text-white btn-outline-primary" id="goToMetrics">
                <i class="fas fa-chart-bar pr-2"></i>Metrics
            </button>
            <button type="button" class="btn text-white btn-outline-primary" id="goToEvents">
                <i class="fas fa-book-open pr-2"></i>Events
            </button>
            <div class="btn-toolbar  mb-2 mb-md-0">

            </div>
        </div>
    </div>
<div id="containerDetails" class="instanceViewBox">
<div class="row">
    <div class="col-md-5">
        <div class="card text-white bg-dark">
          <div class="card-body">
              <h5> <u> Instance Details <i class="fas float-right fa-info-circle"></i> </u> </h5>
              Host: <span id="container-hostNameDisplay"></span>
              <br/>
              Project: <span id="instanceProject"></span>
              <br/>
              Image: <span class="d-inline" id="container-imageDescription"></span>
              <br/>
              <a
                  href="https://github.com/lxc/pylxd/issues/242#issuecomment-323272318"
                  target="_blank">CPU Time:</a> <span id="container-cpuTime"></span>
              <br/>
              Created: <span id="container-createdAt"></span>
              <br/>
              Up Time: <span id="container-upTime"></span>
              <br/>
              Deployment: <span id="container-deployment"></span>
              <br/>
              Comment <button class="btn btn-sm btn-outline-primary ml-1 mr-1" id="editInstanceComment"><i class="fas fa-edit"></i></button>: <span id="container-comment"></span>
          </div>
        </div>
        <div class="card text-white bg-dark">
          <div class="card-body">
            <h5> <u> Network Information <i class="fas float-right fa-network-wired"></i> </u> </h5>
                <div class="col-md-12" id="networkDetails">
                </div>

          </div>
</div>
    </div>
    <div class="col-md-4">
        <div class="card bg-dark">
            <div class="card-body" id="memoryDataCard">

            </div>
        </div>
        <div class="card bg-dark">
            <div class="card-body" id="storageDataCard">

            </div>
        </div>

    </div>
    <div class="col-md-3">
        <div class="card bg-dark">

            <div class="card-body table-responsive">
                <h5 class="text-white">
                    <u> Profiles </u>
                    <i class="fas fa-users float-right"></i>
                </h5>
                <table class="table table-dark table-bordered"id="profileData">
                      <thead class="thead-inverse">
                          <tr>
                              <th> Name </th>
                              <th></th>
                          </tr>
                      </thead>
                      <tbody>
                      </tbody>
                </table>
            </div>
        </div>
        <div class="card bg-dark">
            <div class="card-body table-responsive">
                <h5 class="text-white">
                    <u>Snapshots</u>
                    <i class="fas fa-images float-right"></i>
                </h5>
                <table class="table table-dark table-bordered"id="snapshotData">
                      <thead class="thead-inverse">
                          <tr>
                              <th> Name </th>
                          </tr>
                      </thead>
                      <tbody>
                      </tbody>
                </table>
          </div>
        </div>
        <div class="card bg-dark">
            <div class="card-body table-responsive">
                <h5 class="text-white">
                    <u>Limits</u>
                    <i class="fas fa-user-secret float-right"></i>
                </h5>
                <table class="table table-dark table-bordered" id="limitsTable">
                      <thead class="thead-inverse">
                          <tr>
                              <th> Key </th>
                              <th> Value </th>
                          </tr>
                      </thead>
                      <tbody>
                      </tbody>
                </table>
          </div>
        </div>
    </div>
</div>
</div>
<div id="containerConsole" class="instanceViewBox">
    <div id="terminal-container"></div>
</div>
<div id="containerTerminal" class="instanceViewBox">
    <div class="row">
        <div class="col-md-12 text-center">
            <div id="spice-area">
                <div id="spice-screen" class="spice-screen">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="containerBackups" class="instanceViewBox">
    <div class="row" id="backupErrorRow">
        <div class="col-md-12 alert alert-danger" id="backupErrorMessage">
        </div>
    </div>
    <div class="row" id="backupDetailsRow">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark">
                    <h4> LXDMosaic Instance Backups </h4>
                </div>
                <div class="card-body bg-dark">
                    <table class="table table-bordered table-dark" id="localBackupTable">
                        <thead>
                            <tr>
                                <th> Backup </th>
                                <th> Date </th>
                                <th> Size </th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark">
                    <h4>
                        LXD Instance Backups
                        <button class="btn btn-success float-right" id="createBackup">
                            Create
                        </button>
                    </h4>
                </div>
                <div class="card-body bg-dark">
                    <table class="table table-bordered table-dark" id="remoteBackupTable">
                        <thead>
                            <tr>
                                <th> Backup </th>
                                <th> Date </th>
                                <th> Stored Locally </th>
                                <th> Import </th>
                                <th> Delete </th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="containerFiles"  class="col-md-12 instanceViewBox">
    <div class="alert alert-danger">
        Do not use this over a metered internet connection.
        To Correctly indentify the whether the something is a dir or a file
        we have to get the file and check the response, so the file is "downloaded".
        <br/>
        <br/>
        This will also probably <b> underperform or break </b> on large directories until
        LXD changes the directory struct indictating if its a file or directory
    </div>
    <div class="alert alert-info">
        You can right click to delete a file / folder
        <br/>
        You can right click between the files /  folders to upload new files
    </div>
    <div class="card-columns" id="filesystemTable">
    </div>
</div>
<div id="containerEvents"  class="col-md-12 instanceViewBox">
    <div class="card bg-dark text-white">
        <div class="card-header">
            <h4> Event Logs </h4>
        </div>
        <div class="card-body">
            <table class="table table-dark table-bordered" id="containerEventsTable">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Date</th>
                        <th>Event</th>
                        <th>Parameters</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div id="containerMetrics"  class="col-md-12 instanceViewBox">
    <div class="card bg-dark text-white">
        <div class="card-header">
            Metric Graph
            <select class="float-right" id="metricRangePicker" disabled>
                <option value="">Please Select</option>
                <option value="-15 minutes">Last 15 Minutes</option>
                <option value="-30 minutes">Last 30 Minutes</option>
                <option value="-45 minutes">Last 45 Minutes</option>
                <option value="-60 minutes">Last 60 Minutes</option>
                <option value="-3 hours">Last 3 Hours</option>
                <option value="-6 hours">Last 6 Hours</option>
                <option value="-12 hours">Last 12 Hours</option>
                <option value="-24 hours">Last 24 Hours</option>
                <option value="-2 days">Last 2 Days</option>
                <option value="-3 days">Last 3 Days</option>
                <option value="-1 weeks">Last 1 Week</option>
                <option value="-2 weeks">Last 2 Weeks</option>
                <option value="-3 weeks">Last 3 Weeks</option>
                <option value="-4 weeks">Last 4 Weeks</option>
                <option value="-1 months">Last 1 Month</option>
                <option value="-2 months">Last 2 Months</option>
            </select>
            <select class="float-right form-control-sm" id="metricTypeFilterSelect" disabled>
            </select>
            <select class="float-right" id="metricTypeSelect">
            </select>


        </div>
        <div class="card-body bg-dark" id="metricGraphBody">

        </div>
    </div>

</div>
</div>
<script src="/assets/dist/xterm.js"></script>
<script>

var term = new Terminal();
var consoleSocket;
var currentTerminalProcessId = null;

function loadContainerViewAfter(data = null, milSeconds = 2000)
{
    setTimeout(function(){
        let p = currentContainerDetails;
        if($.isPlainObject(data)){
            p = data;
        }
        loadContainerView(p);
    }, 2000);
}

function loadContainerTreeAfter(milSeconds = 2000, hostId = null, hostAlias = null)
{
    setTimeout(function(){
        let p = $.isNumeric(hostId) ? hostId : currentContainerDetails.hostId;
        let a = hostAlias == null ? currentContainerDetails.alias : hostAlias;
        addHostContainerList(p, a);
    }, milSeconds);
}

function deleteFilesystemObjectConfirm(path)
{
    $.confirm({
        title: `Delete From - ${currentContainerDetails.alias} / ${currentContainerDetails.container} `,
        content: `
            ${path}
            `,
        buttons: {
            cancel: function(){},
            rename: {
                text: 'Delete',
                btnClass: 'btn-danger',
                action: function () {
                    let modal = this;
                    let btn  = $(this);

                    modal.buttons.rename.setText('<i class="fa fa-cog fa-spin"></i>Deleeting..'); // let the user know
                    modal.buttons.rename.disable();
                    modal.buttons.cancel.disable();

                    let x = {
                        ...{path: path},
                        ...currentContainerDetails
                    };

                    ajaxRequest(globalUrls.instances.files.delete, x, function(data){
                        let x = makeToastr(data);
                        if(x.state == "error"){
                            modal.buttons.rename.setText('Delete'); // let the user know
                            modal.buttons.rename.enable();
                            modal.buttons.cancel.enable();
                            return false;
                        }
                        loadFileSystemPath(currentPath);
                        modal.close();
                    });
                    return false;
                }
            },
        }
    });
}
function restoreBackupContainerConfirm(backupId, hostAlias, container, callback = null, wait = true)
{
    $.confirm({
        title: `Backup Instance - ${hostAlias} / ${container} `,
        content: `
            <div class="form-group">
                <label> Target Host </label>
                <input class="form-control" name="targetHost"/>
            </div>
            `,
        buttons: {
            cancel: function(){},
            rename: {
                text: 'Restore',
                btnClass: 'btn-warning',
                action: function () {
                    let modal = this;
                    let btn  = $(this);

                    let targetHost = this.$content.find('input[name=targetHost]').tokenInput("get");

                    if(targetHost.length == 0){
                        $.alert("Please select target host");
                        return false;
                    }

                    modal.buttons.rename.setText('<i class="fa fa-cog fa-spin"></i>Restoring..'); // let the user know
                    modal.buttons.rename.disable();
                    modal.buttons.cancel.disable();

                    let x = {
                        backupId: backupId,
                        targetHost: targetHost[0].hostId
                    }

                    ajaxRequest(globalUrls.backups.restore, x, (data)=>{
                        data = makeToastr(data);
                        if(data.state == "error"){
                            modal.buttons.rename.setText('Backup'); // let the user know
                            modal.buttons.rename.enable();
                            modal.buttons.cancel.enable();
                            return false;
                        }
                        if($.isFunction(callback)){
                            callback.call();
                        }
                        modal.close();
                    });
                    return false;
                }
            },
        },
        onContentReady: function () {
            // bind to events
            var jc = this;
            this.$content.find('input[name=targetHost]').tokenInput(globalUrls.hosts.search.search, {
                queryParam: "hostSearch",
                propertyToSearch: "host",
                tokenValue: "hostId",
                preventDuplicates: false,
                tokenLimit: 1,
                theme: "facebook"
            });
        }
    });
}
function backupContainerConfirm(hostId, hostAlias, container, callback = null, wait = true)
{
    $.confirm({
        title: `Backup Instance - ${hostAlias} / ${container} `,
        content: `
            <div class="form-group">
                <label> Backup Name </label>
                <input class="form-control validateName" maxlength="63" name="name"/>
            </div>
            <div class="form-check">
              <input type="checkbox" class="form-check-input" name="importAndDelete">
              <label class="form-check-label" for="importAndDelete">Import & Delete From Remote</label>
            </div>
            <div class="alert alert-warning">
                This will block the browser while it runs, schedule a backup to
                run in the background! (Or open a new tab)
            </div>
            `,
        buttons: {
            cancel: function(){},
            rename: {
                text: 'Backup',
                btnClass: 'btn-blue',
                action: function () {
                    let modal = this;
                    let btn  = $(this);

                    let backupName = this.$content.find('input[name=name]').val();

                    if(backupName == ""){
                        $.alert('provide a backup name');
                        return false;
                    }

                    let importAndDelete = this.$content.find('input[name=importAndDelete]').is(":checked") ? 1 : 0

                    modal.buttons.rename.setText('<i class="fa fa-cog fa-spin"></i>Backing Up..'); // let the user know
                    modal.buttons.rename.disable();
                    modal.buttons.cancel.disable();

                    let x = {
                        hostId: hostId,
                        container: container,
                        backup: backupName,
                        wait: wait,
                        importAndDelete: importAndDelete
                    }

                    ajaxRequest(globalUrls.instances.backups.backup, x, function(data){
                        let x = makeToastr(data);
                        if(x.state == "error"){
                            modal.buttons.rename.setText('Backup'); // let the user know
                            modal.buttons.rename.enable();
                            modal.buttons.cancel.enable();
                            return false;
                        }
                        if($.isFunction(callback)){
                            callback.call();
                        }
                        modal.close();
                    });
                    return false;
                }
            },
        }
    });
}

function deleteContainerConfirm(hostId, hostAlias, container)
{
    $.confirm({
        title: 'Delete Instance ' + hostAlias + '/' + container,
        content: 'Are you sure you want to delete this instance ?!',
        buttons: {
            cancel: function () {},
            delete: {
                btnClass: 'btn-danger',
                action: function () {
                    let x = {
                        hostId: hostId,
                        container: container
                    }
                    ajaxRequest(globalUrls.instances.delete, x, function(data){
                        let r = makeToastr(data);

                        if(r.state == "success"){
                            if(currentContainerDetails != null){
                                loadServerView(hostId);
                            }
                            loadContainerTreeAfter(1000, hostId, hostAlias);
                            currentContainerDetails = null;
                        }
                    });
                }
            }
        }
    });
}

function editInstanceComment(hostId, hostAlias, container)
{
    $.confirm({
        title: 'Set ' + hostAlias + '/' + container + ' Comment',
        content: `<div class="form-group">
            <label>Comment</label>
            <textarea class="form-control" name="comment"></textarea>
        </div>
        `,
        buttons: {
            cancel: function () {},
            set: {
                btnClass: 'btn-success',
                action: function () {
                    let x = {
                        hostId: hostId,
                        container: container,
                        comment: this.$content.find('textarea[name=comment]').val()
                    };
                    ajaxRequest(globalUrls.instances.comment.set, x, function(data){
                        let r = makeToastr(data);

                        if(r.state == "success"){
                            loadContainerView(currentContainerDetails);
                        }
                    });
                }
            }
        }
    });
}

function renameContainerConfirm(hostId, container, reloadView, hostAlias)
{
    $.confirm({
        title: 'Rename Instance!',
        content: `
            <div class="form-group">
                <label> New Name </label>
                <input class="form-control validateName" maxlength="63" name="name"/>
            </div>`,
        buttons: {
            cancel: function(){},
            rename: {
                text: 'Rename',
                btnClass: 'btn-blue',
                action: function () {
                    let modal = this;
                    let btn  = $(this);

                    let newName = this.$content.find('input[name=name]').val();

                    if(newName == ""){
                        $.alert('provide a new name');
                        return false;
                    }

                    modal.buttons.rename.setText('<i class="fa fa-cog fa-spin"></i>Renaming..'); // let the user know
                    modal.buttons.rename.disable();
                    modal.buttons.cancel.disable();

                    let x = {
                        newContainer: newName,
                        hostId: hostId,
                        container: container
                    }

                    ajaxRequest(globalUrls.instances.rename, x, function(data){
                        let x = makeToastr(data);
                        if(x.state == "error"){
                            return false;
                        }
                        modal.close();
                        addHostContainerList(hostId, hostAlias);
                        if(reloadView){
                            currentContainerDetails.container = newName;
                            loadContainerView(currentContainerDetails);
                        }

                    });
                    return false;
                }
            },
        }
    });
}

function snapshotContainerConfirm(hostId, container)
{
    $.confirm({
        title: 'Snapshot Instance - ' + container,
        content: `
            <div class="form-group">
                <label> Snapshot Name </label>
                <input class="form-control validateName" maxlength="63" name="name"/>
            </div>`,
        buttons: {
            cancel: function(){},
            rename: {
                text: 'Take Snapshot',
                btnClass: 'btn-blue',
                action: function () {
                    let modal = this;
                    let btn  = $(this);

                    let snapshotName = this.$content.find('input[name=name]').val();

                    if(snapshotName == ""){
                        $.alert('provide a snapshot name');
                        return false;
                    }

                    modal.buttons.rename.setText('<i class="fa fa-cog fa-spin"></i>Taking snapshot..'); // let the user know
                    modal.buttons.rename.disable();
                    modal.buttons.cancel.disable();

                    let x = {
                        hostId: hostId,
                        container: container,
                        snapshotName: snapshotName
                    }

                    ajaxRequest(globalUrls.instances.snapShots.take, x, function(data){
                        let x = makeToastr(data);
                        if(x.state == "error"){
                            return false;
                        }
                        modal.close();
                    });
                    return false;
                }
            },
        }
    });
}

function copyContainerConfirm(hostId, container) {
    $.confirm({
        title: 'Copy Container!',
        content: `
            <div class="form-group">
                <label> Destination </label>
                <select class="form-control" name="destination"></select>
            </div>
            <div class="form-group">
                <label> Name </label>
                <input class="form-control validateName" maxlength="63" name="name"/>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="copyInstanceProfiles">
                <label class="form-check-label" for="copyInstanceProfiles">
                    Copy Profiles
                </label>
            </div>
            `,
        buttons: {
            cancel: function(){},
            copy: {
                text: 'Copy',
                btnClass: 'btn-blue',
                action: function () {
                    let modal = this;

                    let option = modal.$content.find("select[name=destination] option:selected");
                    let optGroup = option.parent("optgroup");

                    if(optGroup.length == 0){
                        makeToastr({state: 'error', message: "Select Destination"})
                        return false;
                    }

                    let newHostId = optGroup.attr("id");
                    let targetProject = option.val();

                    modal.buttons.copy.setText('<i class="fa fa-cog fa-spin"></i>Copying..'); // let the user know
                    modal.buttons.copy.disable();
                    modal.buttons.cancel.disable();

                    let x = {
                        newContainer: modal.$content.find("input[name=name]").val(),
                        copyProfiles: modal.$content.find("input[id=copyInstanceProfiles]").is(":checked") ? 1 : 0,
                        newHostId: newHostId,
                        hostId: hostId,
                        container: container,
                        targetProject: targetProject
                    };

                    ajaxRequest(globalUrls.instances.copy, x, function(data){
                        let x = makeToastr(data);
                        if(x.state == "error"){
                            modal.buttons.copy.enable();
                            modal.buttons.cancel.enable();
                            modal.buttons.copy.setText('Copy'); // let the user know
                            return false;
                        }
                        loadContainerTreeAfter();
                        modal.close();
                    });
                    return false;
                }
            },
        },
        onContentReady: function () {
            var jc = this;
            ajaxRequest(globalUrls.projects.getAllFromHosts, {}, function(data){
                data = $.parseJSON(data);
                let options = "<option value=''>Please select</option>";
                $.each(data.clusters, (clusterIndex, cluster)=>{
                    options += `<li class="c-sidebar-nav-title text-success pl-1 pt-2"><u>Cluster ${clusterIndex}</u></li>`;
                    $.each(cluster.members, (_, host)=>{
                        if(host.hostOnline == 0){
                            return true;
                        }
                        options += `<optgroup id="${host.hostId}" label="${host.alias}">`
                        $.each(host.projects, (project, _)=>{
                            options += `<option value="${project}">${project}</option>`
                        });
                        options += `</optgroup>`
                    })
                });

                $.each(data.standalone.members, (_, host)=>{
                    if(host.hostOnline == 0){
                        return true;
                    }
                    options += `<optgroup id="${host.hostId}" label="${host.alias}">`
                    $.each(host.projects, (_, project)=>{
                        options += `<option value="${project}">${project}</option>`
                    });
                    options += `</optgroup>`
                });
                jc.$content.find("select[name=destination]").empty().append(options);
            });
        }
    });
}

function loadContainerBackups()
{
    ajaxRequest(globalUrls.instances.backups.getContainerBackups, currentContainerDetails, (data)=>{
        x = makeToastr(data);
        $("#backupDetailsRow").show();
        $("#backupErrorRow").hide()
        if(x.hasOwnProperty("state") && x.state == "error"){
            $("#backupErrorRow").show()
            $("#backupErrorMessage").text(x.message);
            $("#backupDetailsRow").hide();
            return false;
        }

        let localBackups = "";

        if(x.localBackups.length > 0 ){
            $.each(x.localBackups, function(_, item){
                localBackups += `<tr data-backup-id="${item.id}">
                    <td>${item.backupName}</td>
                    <td>${moment(item.dateCreated).fromNow()}</td>
                    <td>${formatBytes(item.filesize)}</td>
                </tr>`
            });
        } else{
            localBackups = `<tr><td colspan="999" class="text-center text-info">No backups</td></tr>`
        }

        $("#localBackupTable > tbody").empty().append(localBackups);

        let remoteBackups = "";
        if(x.remoteBackups.length > 0){
            $.each(x.remoteBackups, function(_, item){

                let trClass = 'danger',
                    downloadedLocallySym = '<i class="fas fa-times-circle"></i>',
                    importHtml = `<button class="btn btn-primary importBackup">Import</button>`;

                if(item.storedLocally){
                    trClass = 'success';
                    downloadedLocallySym = '<i class="fas fa-check-circle"></i>';
                    importHtml = "<b class='text-info'>Already Imported</b>"
                }

                remoteBackups += `<tr data-name="${item.name}" class="alert alert-${trClass}">
                    <td>${item.name}</td>
                    <td>${moment(item.created_at).fromNow()}</td>
                    <td>${downloadedLocallySym}</td>
                    <td>${importHtml}</td>
                    <td><button class='btn btn-danger deleteBackup'><i class="fas fa-trash"></i></button></td>
                </tr>`
            });
        }else{
            remoteBackups = `<tr><td colspan="999" class="text-center text-info">No backups</td></tr>`
        }


        $("#remoteBackupTable > tbody").empty().append(remoteBackups);
    });

}

function loadContainerView(data)
{
    $(".instanceViewBox").hide();
    $("#containerDetails").show();
    $("#goToDetails").trigger("click");
    if(consoleSocket !== undefined && currentTerminalProcessId !== null){
        consoleSocket.close();
        currentTerminalProcessId = null;
    }

    window.disconnectFromTerminal();

    $("#goToMetrics").attr("disabled", true).addClass("disabled").data({
        toggle: "tooltip",
        placement: "bottom",
        title: 'Go To Server View & Enable Gather Metrics!'
    });

    ajaxRequest(globalUrls.instances.getInstance, data, function(result){
        let x = $.parseJSON(result);

        if(x.state == "error"){
            makeToastr(result);
            return false;
        }
        changeActiveNav(".overview");
        addBreadcrumbs(["Dashboard", data.alias, data.container ], ["overview", "viewHost lookupId", "active"], false);

        let disableActions = x.state.status_code !== 102;

        let stateBtnsToEnable = [];
        let stateBtnsToDisable = [];

        if(x.state.status_code == 103){
            stateBtnsToEnable = ["stop", "freeze", "restart"];
            stateBtnsToDisable = ["start", "unfreeze"];
        }else if(x.state.status_code == 102){
            stateBtnsToEnable = ["start"];
            stateBtnsToDisable = ["stop", "freeze", "restart", "unfreeze"];
        }else if(x.state.status_code == 110){
            stateBtnsToEnable = ["unfreeze"];
            stateBtnsToDisable = ["start", "stop", "freeze", "restart"];
        }else{
            stateBtnsToEnable = ["start", "unfreeze"];
            stateBtnsToDisable = ["stop", "freeze"];
        }

        $.each(stateBtnsToDisable, (_, i)=>{
            $(`.changeInstanceState[data-action='${i}']`).addClass("bg-secondary disabled").attr("disabled", "disabled");
        })
        $.each(stateBtnsToEnable, (_, i)=>{
            $(`.changeInstanceState[data-action='${i}']`).removeClass("bg-secondary disabled").attr("disabled", false);
        })


        if(x.details.type == "container"){
            $("#goToTerminal").hide();
        }else{
            $("#goToTerminal").show();
        }


        if(x.details.expanded_config.hasOwnProperty("environment.lxdMosaicPullMetrics") || x.haveMetrics){
            $("#goToMetrics").tooltip("disable");
            $("#goToMetrics").attr("disabled", false).removeClass("disabled").data({});
        }else{
            $("#goToMetrics").tooltip("enable");
        }

        $(".renameContainer").attr("disabled", disableActions);
        $(".deleteContainer").attr("disabled", disableActions);

        $("#container-currentState").html(`<i class="` + statusCodeIconMap[x.state.status_code] +`"></i>`);

        if(x.backupsSupported){
            $("#goToBackups").removeClass("bg-dark disabled").css("cursor", "pointer");
        }else{
            $("#goToBackups").addClass("bg-dark disabled").css("cursor", "not-allowed");
        }

        //NOTE Read more here https://github.com/lxc/pylxd/issues/242
        let containerCpuTime = nanoSecondsToHourMinutes(x.state.cpu.usage);

        let os = x.details.config.hasOwnProperty("image.os") ? x.details.config["image.os"] : "<b style='color: #ffc107'>Can't find OS</b>";
        let version = "<b style='color: #ffc107'>Cant find version</b>";
        if(x.details.config.hasOwnProperty("image.version")){
            version = x.details.config["image.version"];
        }else if(x.details.config.hasOwnProperty("image.release")){
            version = x.details.config["image.release"];
        }

        $("#container-hostNameDisplay").text(currentContainerDetails.alias);
        $("#container-containerNameDisplay").text(data.container);
        $("#instanceProject").text(x.project);
        $("#container-imageDescription").html(`${os} (${version})`);
        $("#container-cpuTime").text(containerCpuTime);
        $("#container-createdAt").text(moment(x.details.created_at).format("MMM DD YYYY h:mm A"));

        let limitsTrs = "";

        $.each(x.details.config, (key, value)=>{
            if(key.startsWith("limit")){
                limitsTrs += `<tr>
                    <td>${key}</td>
                    <td>${value}</td>
                </tr>`
            }
        });

        if(limitsTrs == ""){
            limitsTrs = "<tr><td colspan='2' class='text-center'><i class='fas fa-info-circle text-success mr-2'></i>No Limits</td></tr>";
        }


        $("#limitsTable > tbody").empty().append(limitsTrs);

        if(x.details.hasOwnProperty("last_used_at")){
            let last_used_at = moment(x.details.last_used_at);
            if(last_used_at.format("YYYY") == "1970"){
                $("#container-upTime").text("Not Started Yet");
            }else if(!disableActions){
                $("#container-upTime").text("Offline");
            }else{
                let now = moment(new Date());

                var ms = now.diff(last_used_at);
                var d = moment.duration(ms);
                var s = Math.floor(d.asHours()) + moment.utc(ms).format(":mm:ss")
                $("#container-upTime").text(s);
            }
        }else{
            $("#container-upTime").text("LXD Extension Missing");
        }

        let deployment = "Not In Deployment";

        if(x.deploymentDetails !== false){
            deployment = `<a href="#" data-deployment-id="${x.deploymentDetails.id}" class="toDeployment">${x.deploymentDetails.name}</a>`
        }

        $("#container-deployment").html(deployment);

        let userComment = "";

        if(x.details.config.hasOwnProperty("user.comment") !== false){
            userComment = nl2br(x.details.config["user.comment"]);
        }

        $("#container-comment").html(userComment);

        let snapshotTrHtml = "";

        if(x.snapshots.length == 0){
            snapshotTrHtml = "<tr><td colspan='999' class='text-center'> No snapshots </td></tr>"
        }else{
            $.each(x.snapshots, function(i, item){
                snapshotTrHtml += `<tr><td><a href='#' id='${item}' class='viewSnapsnot'> ${item} </a></td></tr>`;
            });
        }

        $("#snapshotData >  tbody").empty().append(snapshotTrHtml);

        let profileTrHtml = "";

        if(x.details.profiles.length == 0){
            profileTrHtml = "<tr><td colspan='999' class='text-center'> No Profiles </td></tr>"
        }else{
            $.each(x.details.profiles, function(i, item){
                profileTrHtml += `<tr data-profile="${item}">
                    <td><a href='#' data-profile=${item} class='toProfile'>${item}</a></td>
                    <td><button class='btn btn-sm btn-outline-danger removeProfile'><i class="fas fa-trash"></i></button></td>
                </tr>`;
            });
        }

        $("#profileData >  tbody").empty().append(profileTrHtml);

        let networkData = "";

        if(x.state.network !== null){
            $.each(x.state.network,  function(i, item){
                if(i == "lo"){
                    return;
                }
                networkData += `<div class='padding-bottom: 2em;'><b>${i}:</b><br/>`;
                let lastKey = item.addresses.length - 1;
                $.each(item.addresses, function(i, item){
                    networkData += `<span style='padding-left:3em'>${item.address}<br/></span>`;
                });
                networkData += "</div>";
            });

            if(networkData == ""){
                networkData = '<div class="text-center"><i class="fas fa-info-circle text-info mr-2"></i>Only local interface present!</div>';
            }
        }else{
            networkData = '<div class="text-center"><i class="fas fa-info-circle text-info mr-2"></i>Instance Offline</div>';
        }

        $("#networkDetails").empty().append(networkData);

        let memoryLabels = [],
            memoryColors = [],
            memoryData = [];

        $.each(x.state.memory, function(i, item){
            memoryLabels.push(i);
            memoryColors.push(randomColor());
            memoryData.push(item);
        });

        if(x.state.status_code == 103){
            $("#memoryDataCard").empty().append(`
                <h5 class="text-white">
                    <u> Memory Usage </u>
                    <i class="fas fa-memory float-right"></i>
                </h5>
                <div style="width: 100%;">
                <canvas id="memoryData" height="200"></canvas></div>`);

            new Chart($("#memoryData"), {
                type: "bar",
                data: {
                    labels: memoryLabels,
                    datasets: [{
                      label: 'Memory',
                      data: memoryData,
                      backgroundColor: memoryColors,
                      borderColor: memoryColors,
                      borderWidth: 1
                  }]
                },
                options: {
                  cutoutPercentage: 40,
                  responsive: false,
                  scales: scalesBytesCallbacks,
                  tooltips: toolTipsBytesCallbacks
                }
            });

            $("#storageDataCard").empty().append(`
                <h5 class="text-white">
                    <u> Disk Usage </u>
                    <i class="fas fa-hdd float-right"></i>
                </h5>
                <div style="width: 100%;">
                <canvas id="storageData" height="200"></canvas></div>`);


            let storageKeys = Object.keys(x.state.disk);
            let storageColors = [];
            let storageLabels = storageKeys;
            let storageData = storageKeys.map((key)=>{
                storageColors.push(randomColor());
                return x.state.disk[key].usage;
            });

            new Chart($("#storageData"), {
                type: "bar",
                data: {
                    labels: storageLabels,
                    datasets: [{
                      label: 'Space Used',
                      data: storageData,
                      backgroundColor: storageColors,
                      borderColor: storageColors,
                      borderWidth: 1
                  }]
                },
                options: {
                  responsive: false,
                  scales: scalesBytesCallbacks,
                  tooltips: toolTipsBytesCallbacks
                }
            });
        }else{
            $("#memoryDataCard").empty().append(`<h5 class="text-white">
                <u> Memory Usage </u>
                <i class="fas fa-memory float-right"></i>
            </h5>
            <div class="text-center"><i class="fas fa-info-circle text-info mr-2"></i>Instance Offline</div>`);

            $("#storageDataCard").empty().append(`<h5 class="text-white">
                <u> Disk Usage </u>
                <i class="fas fa-hdd float-right"></i>
            </h5>
            <div class="text-center"><i class="fas fa-info-circle text-info mr-2"></i>Instance Offline</div>`);
        }



        $(".boxSlide").hide();
        $("#containerBox").show();
        $('html, body').animate({scrollTop:0},500);
    });
}

$("#containerBox").on("click", ".removeProfile", function(){
    console.log(currentContainerDetails);
    let tr = $(this).parents("tr");
    let profile = tr.data("profile");
    ajaxRequest(globalUrls.instances.profiles.remove, {...{profile: profile}, ...currentContainerDetails}, (data)=>{
        data = makeToastr(data);
        if(data.state == "error"){
            return false;
        }
        tr.remove();
    });
});

$("#containerBox").on("click", "#createBackup", function(){
    backupContainerConfirm(
        currentContainerDetails.hostId,
        currentContainerDetails.alias,
        currentContainerDetails.container,
        loadContainerBackups
    );
});

$("#containerBox").on("click", ".containerRestoreBackup", function(){
    let backupId = $(this).parents("tr").data("backupId");

    restoreBackupContainerConfirm(
        backupId,
        currentContainerDetails.alias,
        currentContainerDetails.container
    );

});

$("#containerBox").on("click", ".deleteBackup", function(){

    let x = {
        hostId: currentContainerDetails.hostId,
        container: currentContainerDetails.container,
        backup: $(this).parents("tr").data("name")
    }

    $.confirm({
        title: `Delete Backup - ${currentContainerDetails.alias} / ${currentContainerDetails.container} / ${x.backup} `,
        content: ``,
        buttons: {
            cancel: function(){},
            delete: {
                text: 'Delete Backup',
                btnClass: 'btn-danger',
                action: function () {
                    let modal = this;
                    let btn  = $(this);

                    modal.buttons.delete.setText('<i class="fa fa-cog fa-spin"></i>Deleting..'); // let the user know
                    modal.buttons.delete.disable();
                    modal.buttons.cancel.disable();

                    ajaxRequest(globalUrls.instances.backups.deleteContainerBackup, x, (data)=>{
                        data = makeToastr(data);
                        if(x.state == "error"){
                            modal.buttons.delete.setText('Delete Backup'); // let the user know
                            modal.buttons.delete.enable();
                            modal.buttons.cancel.enable();
                            return false;
                        }else{
                            modal.close();
                            loadContainerBackups();
                        }
                    });
                    return false;
                }
            }
        }
    });
});

$("#containerBox").on("click", ".importBackup", function(){
    let tr =  $(this).parents("tr");
    $.confirm({
        title: `Import Backup - ${currentContainerDetails.alias} / ${currentContainerDetails.container} / ${tr.data("name")} `,
        content: `
            <div class="form-check">
              <input type="checkbox" class="form-check-input" name="delete">
              <label class="form-check-label" for="delete">Delete From Remote?</label>
            </div>
            `,
        buttons: {
            cancel: function(){},
            rename: {
                text: 'Import',
                btnClass: 'btn-blue',
                action: function () {
                    let modal = this;
                    let btn  = $(this);

                    let deleteFromRemote = this.$content.find('input[name=delete]').is(":checked") ? 1 : 0

                    modal.buttons.rename.setText('<i class="fa fa-cog fa-spin"></i>Importing..'); // let the user know
                    modal.buttons.rename.disable();
                    modal.buttons.cancel.disable();

                    let x = {
                        hostId: currentContainerDetails.hostId,
                        container: currentContainerDetails.container,
                        backup: tr.data("name"),
                        'delete': deleteFromRemote
                    }

                    ajaxRequest(globalUrls.instances.backups.importContainerBackup, x, (data)=>{
                        data = makeToastr(data);
                        if(data.state == "error"){
                            modal.buttons.rename.setText('Importing'); // let the user know
                            modal.buttons.rename.enable();
                            modal.buttons.cancel.enable();
                        }

                        modal.close();
                        loadContainerBackups();
                    });
                    return false;
                }
            },
        }
    });
});

$("#containerBox").on("click", "#editInstanceComment", function(){
    editInstanceComment(currentContainerDetails.hostId, currentContainerDetails.alias, currentContainerDetails.container);
});

$("#containerBox").on("click", ".renameContainer", function(){
    renameContainerConfirm(currentContainerDetails.hostId, currentContainerDetails.container, true, currentContainerDetails.alias);
});

$("#containerViewBtns").on("click", ".btn", function(){
    if($(this).attr("id") == "goToBackups" && $(this).hasClass("disabled")){
        return false;
    }

    $("#containerViewBtns").find(".active").removeClass("active");
    $(this).addClass("active");
});

var currentPath = "/";
var currentRequest = null;

function loadFileSystemPath(path){
    let reqData = {
        ...currentContainerDetails,
        ...{path: path, download: 0}
    };

    currentRequest = $.ajax({
         type: 'POST',
         data: reqData,
         url: globalUrls.instances.files.getPath,
         beforeSend : function()    {
            if(currentRequest != null) {
                currentRequest.abort();
            }
         },
         success: function(data){
             currentRequest = null;
             data = makeToastr(data);


             if(data.hasOwnProperty("state") && data.state == "error"){
                 // pathHistory[pathHistory.length - 1].directory = false;
                 return false;
             }

             if(data.isDirectory){
                 currentPath = path;
                 let h = "";
                 if(path.endsWith("/") !== true){
                     path += "/";
                 }
                 if(path !== "/"){

                     h = `<div class="card bg-dark w-10 goUpDirectory">
                        <div class="card-body text-center">
                            <i class="fas fa-circle fa-3x"></i>
                            <i class="fas fa-circle fa-3x"></i>
                            <h4>Back</h4>
                        </div>
                      </div>`;
                 }
                 $.each(data.contents, function(_, item){
                     let icon = `<i class="fas fa-3x fa-${item.isDirectory ? "folder" : "file"}"></i>`

                     h += `
                     <div class="card filesystemObject bg-dark w-10" data-name="${item.name}" data-path="${path}${item.name}">
                        <div class="card-body text-center">
                            ${icon}
                            <h4>${item.name}</h4>
                        </div>
                      </div>
                     `
                 });
                 $("#filesystemTable").empty().append(h);
             }else {
                 var formData = new FormData();

                 let parts = path.split("/")
                 let fileName = parts[parts.length - 1];

                formData.append("hostId", currentContainerDetails.hostId);
                formData.append("path", path);
                formData.append("container", currentContainerDetails.container);
                formData.append("download", 1);
                // Stolen straight from stackoverflow
                 fetch('/api/Instances/Files/GetPathController/get', {
                     method: 'POST',
                     headers: userDetails,
                     body: formData
                 })
                  .then(resp => resp.blob())
                  .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    // the filename you want
                    a.download = fileName;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                  })
                  .catch(() => alert('oh no something went wrong!'));
             }
         }
     });
}

$("#containerBox").on("click", "#goToFiles", function(){
    $(".instanceViewBox").hide();
    $("#containerFiles").show();
    loadFileSystemPath("/");
});

$("#containerBox").on("change", "#metricTypeSelect", function(){
    let type = $(this).val();
    if(type == ""){
        $("#metricTypeFilterSelect, #metricRangePicker").attr("disabled", true);
        $("#metricGraphBody").empty();
        return false;
    }
    let x = {...{type: type}, ...currentContainerDetails}
    ajaxRequest(globalUrls.instances.metrics.getTypeFilters, x, (data)=>{
        data = $.parseJSON(data);
        let html = "<option value=''>Please select</option>";
        $.each(data, (_, filter)=>{
            html += `<option value='${filter}'>${filter}</option>`
        });
        $("#metricTypeFilterSelect").attr("disabled", false).empty().append(html);
    });
});

$("#containerBox").on("change", "#metricRangePicker", function(){
    if($(this).val() == ""){
        $("#metricGraphBody").empty();
        return false;
    }
    $("#metricTypeFilterSelect").trigger("change");
});

$("#containerBox").on("change", "#metricTypeFilterSelect", function(){
    let filter = $(this).val();
    let type = $("#metricTypeSelect").val();
    let range = $("#metricRangePicker").val();

    if(type == "" || filter == ""){
        $("#metricRangePicker").attr("disabled", true);
        $("#metricGraphBody").empty();
        return false;
    }

    $("#metricRangePicker").attr("disabled", false);

    if(range == ""){
        return false;
    }

    let x = {...{type: type, filter: filter, range: range}, ...currentContainerDetails}
    ajaxRequest(globalUrls.instances.metrics.getGraphData, x, (data)=>{
        let color = randomColor();
        data = $.parseJSON(data);
        $("#metricGraphBody").empty().append('<canvas id="metricGraph" style="width: 100%;"></canvas>');

        let scales = data.formatBytes ? scalesBytesCallbacks : {yAxes: [{}]}
        let tooltips = data.formatBytes ? toolTipsBytesCallbacks : [];

        scales.yAxes[0].gridLines = {drawBorder: false}
        let labels = [];
        data.labels.forEach((element) => {
            let d = moment.utc(element).local();
            let f = d.isSame(moment(), 'day') ? "HH:mm" : "MMM Do HH:mm";
            labels.push(d.format(f))
        });

        new Chart($("#metricGraph"), {
            type: "line",
            data: {
                labels: labels,
                datasets: [{
                    label: `Data`,
                    fill: false,
                    borderColor: color,
                    pointHoverBackgroundColor: color,
                    backgroundColor: color,
                    pointHoverBorderColor: color,
                    data: data.data,
                    pointRadius: 0,
                    formatBytes: data.formatBytes,
					lineTension: 0,
					borderWidth: 2
                }]
            },
            options: {
                animation: {
                    duration: 0
                },
                scales:scales,
                tooltips: {
                    intersect: false,
                    mode: 'index',
                    callbacks: {
                        label: function(tooltipItem, myData) {
                            let ds = myData.datasets[tooltipItem.datasetIndex];
                            var label = ds.label || '';

                            if (label) {
                                label += ': ';
                            }
                            if(ds.hasOwnProperty("formatBytes") && ds.formatBytes){
                                label += formatBytes(tooltipItem.value);
                            }else{
                                label += parseFloat(tooltipItem.value).toFixed(2);
                            }

                            return label;
                        }
                    }
                }
            }
        });
    });
});

$("#containerBox").on("click", "#goToMetrics", function(){
    $(".instanceViewBox").hide();
    $("#containerMetrics").show();

    $("#metricGraphBody").empty();
    $("#metricRangePicker").attr("disabled", true);
    $("#metricTypeFilterSelect").attr("disabled", true).empty().append(`<option value=''>Please Select</option>`)
    $("#metricRangePicker").find("option[value='']").attr("selected", true);
    let x = {...{type: 1}, ...currentContainerDetails}

    ajaxRequest(globalUrls.instances.metrics.getAllTypes, currentContainerDetails, (data)=>{
        data = $.parseJSON(data);
        let html = "<option value=''>Please select</option>";
        $.each(data, (_, item)=>{
            html += `<option value='${item.typeId}'>${item.type}</option>`
        });
        $("#metricTypeSelect").empty().append(html);
    });

});

$(document).on("click", ".filesystemObject", function(){
    loadFileSystemPath($(this).data("path"));
});

$(document).on("click", ".goUpDirectory", function(){
    let parts = currentPath.split("/").filter(word => word.length > 0);

    if(parts.length > 1){
        parts.pop();
    }else{
        parts = ["/"];
    }

    let p = parts.join("/")

    loadFileSystemPath(p);
});

$("#containerBox").on("click", "#goToDetails", function(){
    $(".instanceViewBox").hide();
    $("#containerDetails").show();
});

$("#containerBox").on("click", "#goToBackups", function() {
    if($(this).hasClass("disabled")){
        return false;
    }
    loadContainerBackups();
    $(".instanceViewBox").hide();
    $("#containerBackups").show();
});

$("#containerBox").on("click", "#goToEvents", function() {
    $(".instanceViewBox").hide();
    $("#containerEvents").show();
    ajaxRequest('/api/Instances/RecordedActions/GetHostInstanceEventsController/get', currentContainerDetails, (data)=>{
        data = makeToastr(data);
        let trs = "";
        if(data.length > 0){
            $.each(data, (_, instanceEvent)=>{
                trs += `<tr>
                    <td>${instanceEvent.userName}</td>
                    <td>${moment.utc(instanceEvent.date).local().format("llll")}</td>
                    <td>${instanceEvent.controllerName == "" ? instanceEvent.controller : instanceEvent.controllerName}</td>
                    <td>${instanceEvent.params}</td>
                </tr>`
            });
        }else{
            trs = "<tr><td colspan='999' class='text-center'>No Logs</td></tr>"
        }

        $("#containerEventsTable > tbody").empty().append(trs);
    });
});

$("#containerBox").on("click", "#goToTerminal", function() {
    $(".instanceViewBox").hide();
    $("#containerTerminal").show();

    $.confirm({
        title: 'What Size Monitor?!',
        content: `What size monitor do you plan on using?`,
        buttons: {
            back: function(){
                $("#goToDetails").trigger("click");
            },
            go: {
                text: "800x640",
                keys: ['enter'],
                btnClass: "btn-success",
                action: function(){
                    $("#spice-screen").append(`<h4 id="spiceLoadingIndicator"> <i class="fas fa-cog fa-spin"></i> </h4>`)
                    let project = $("#instanceProject").text();

                    window.disconnectFromTerminal();
                    window.connectToTerminal(undefined, currentContainerDetails.hostId, project, currentContainerDetails.container);
                }
            },
            goLarge: {
                text: "> 800x640 (Opens New Tab)",
                btnClass: "btn-primary",
                action: function(){
                    let project = $("#instanceProject").text();
                    let x = {hostId: currentContainerDetails.hostId, project: project, instance: currentContainerDetails.container};
                    window.open("/terminal?" + $.param(x), "_blank");
                    $("#goToDetails").trigger("click");
                }
            }
        }
    });

});

$("#containerBox").on("click", "#goToConsole", function() {
    Terminal.applyAddon(attach);

    $(".instanceViewBox").hide();
    $("#containerConsole").show();


    if(currentTerminalProcessId === null){
        const terminalContainer = document.getElementById('terminal-container');
        // Clean terminal
        while (terminalContainer.children.length) {
            terminalContainer.removeChild(terminalContainer.children[0]);
        }

        $.confirm({
            title: 'Instance Shell!',
            content: `
                <div class="form-group">
                    <label> Shell </label>
                    <input class="form-control" value="bash" maxlength="63" name="shell"/>
                </div>
                `,
            buttons: {
                cancel: function(){
                    $("#goToDetails").trigger("click");
                },
                go: {
                    text: "Go!",
                    btnClass: "btn-primary",
                    action: function(){


                        let shell = this.$content.find("input[name=shell]").val();

                        if(shell == ""){
                            $.alert("Please input a shell");
                            return false;
                        }

                        term = new Terminal({});
                        let project = $("#instanceProject").text();

                        term.open(terminalContainer);

                        // fit is called within a setTimeout, cols and rows need this.
                        setTimeout(() => {
                            $.ajax({
                                type: "POST",
                                dataType: 'json',
                                contentType: 'application/json',
                                url: '/terminals?cols=' + term.cols + '&rows=' + term.rows,
                                data: JSON.stringify({
                                    host: currentContainerDetails.hostId,
                                    container: currentContainerDetails.container
                                }),
                                success: function(data) {
                                    currentTerminalProcessId = data.processId;

                                    // Theoretically no need to inject credentials
                                    // here as auth is only called when a socket
                                    // is first connected (in this case when the
                                    // operations socket is setup - which will
                                    // always come before this) but to be safe ...
                                    consoleSocket = new WebSocket(`wss://${getQueryVar("host", window.location.hostname)}:${getQueryVar("port", 443)}/node/console?${$.param($.extend({
                                        ws_token: userDetails.apiToken,
                                        user_id: userDetails.userId,
                                        pid: data.processId,
                                        shell: shell,
                                        userId: userDetails.userId,
                                        host: currentContainerDetails.hostId,
                                        instance: currentContainerDetails.container,
                                        project: project
                                    }, currentContainerDetails))}`);

                                    term.attach(consoleSocket);
                                },
                                error: function(){
                                    makeNodeMissingPopup();
                                },
                                dataType: "json"
                            });
                        }, 0);
                    }
                }
            }
        });
    }

});

$("#containerBox").on("click", ".toDeployment", function(){
    let deploymentId = $(this).data("deploymentId");
    loadDeploymentsView(deploymentId);
    changeActiveNav(".viewDeployments")
})

$("#containerBox").on("click", ".copyContainer", function(){
    copyContainerConfirm(currentContainerDetails.hostId, currentContainerDetails.container);
});

$("#containerBox").on("click", ".migrateContainer", function(){
    $("#modal-container-migrate").modal({
        backdrop: 'static',
        keyboard: false
    });
});

$("#containerBox").on("click", ".takeSnapshot", function(){
    snapshotContainerConfirm(currentContainerDetails.hostId, currentContainerDetails.container);
});

$("#containerBox").on("click", ".editContainerSettings", function(){
    $("#modal-container-editSettings").modal("show");
});

$("#containerBox").on("click", "#assignProfilesBtn", function(){
    $("#modal-container-assignProfiles").modal("show");
});

$("#containerBox").on("click", "#attachVolumesBtn", function(){
    $("#modal-container-attachVolumes").modal("show");
});

$("#containerBox").on("click", "#craeteImage", function(){
    $("#modal-container-createImage").modal("show");
});

$("#containerBox").on("click", ".deleteContainer", function(){
    deleteContainerConfirm(currentContainerDetails.hostId, currentContainerDetails.alias, currentContainerDetails.container);
});

$("#containerBox").on("click", ".changeInstanceState", function(){
    let url = globalUrls.instances.state[$(this).data("action")];
    $(".changeInstanceState").tooltip("hide");
    ajaxRequest(url, currentContainerDetails, function(data){
        let result = makeToastr(data);
        loadContainerTreeAfter();
        loadContainerViewAfter();
    });
});

$("#containerBox").on("click", ".viewSnapsnot", function(){
    snapshotDetails.snapshotName = $(this).attr("id");
    $("#modal-container-restoreSnapshot").modal("show");
});
</script>
<?php
    require __DIR__ . "/../modals/containers/migrateContainer.php";
    require __DIR__ . "/../modals/containers/restoreSnapshot.php";
    require __DIR__ . "/../modals/containers/createContainer.php";
    require __DIR__ . "/../modals/containers/editSettings.php";
    require __DIR__ . "/../modals/containers/files/uploadFile.php";
    require __DIR__ . "/../modals/instances/vms/createVm.php";
    require __DIR__ . "/../modals/containers/createImage.php";
    require __DIR__ . "/../modals/containers/assignProfiles.php";
    require __DIR__ . "/../modals/instances/attachVolumes.php";
?>
