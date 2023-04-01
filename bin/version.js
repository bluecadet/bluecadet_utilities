var path = require('path');
const fs = require('fs');
const date = require('date-and-time');
const yargs = require('yargs/yargs');
const { hideBin } = require('yargs/helpers');
const argv = yargs(hideBin(process.argv)).argv;
const semver = require('semver');
const { exec } = require("child_process");

// Create new text.
const project = "external_site_monitor";
const now = new Date();
const version = argv._[0];

if (!semver.valid(version)) {
  console.error("Version is not semantically correct");
  process.exit(1);
}

let bldMsg = "# Information added by packaging script on " + date.format(now, 'YYYY-MM-DD') + "\r\n";
bldMsg += "version: " + version + "\r\n";
bldMsg += "project: " + project + "\r\n";
bldMsg += "datestamp: " + Date.now();

let infoFiles = [];
fromDir('./', /\.info.yml$/, function(filename) {
  infoFiles.push(filename);
});

infoFiles.forEach((filename) => {
  fs.readFile(filename, 'utf8', (err, data) => {
    if (err) {
      console.error(err);
      return;
    }

    const index = data.indexOf("# Information added by packaging script on ");

    if (index == -1) {
      data += "\r\n\r\n" + bldMsg;
    }
    else {
      data = data.substring(0, index) + bldMsg;
    }

    fs.writeFile(filename, data, { flag: 'w+' }, err => {
      if (err) console.log(err);
    });
  });

});




// git tag version
exec("git add . && git commit -m \"Changing version to " + version + "\" && git tag  " + version, execCallback);














function execCallback(error, stdout, stderr) {
    if (error) {
        console.log(`error: ${error.message}`);
        return;
    }
    if (stderr) {
        console.log(`stderr: ${stderr}`);
        return;
    }
    console.log(`stdout: ${stdout}`);
}

function fromDir(startPath, filter, callback) {
    if (!fs.existsSync(startPath)) {
        console.log("no dir ", startPath);
        return;
    }

    var files = fs.readdirSync(startPath);
    for (var i = 0; i < files.length; i++) {
        var filename = path.join(startPath, files[i]);
        var stat = fs.lstatSync(filename);
        if (stat.isDirectory()) {
            fromDir(filename, filter, callback); //recurse
        } else if (filter.test(filename)) callback(filename);
    }
}





// # Information added by Drupal.org packaging script on 2021-03-31
// version: '8.x-1.0-beta6'
// project: 'entity_clone'
// datestamp: 1617210000
