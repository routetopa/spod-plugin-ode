const webshot = require("webshot");
const fs = require("fs");

if(process.argv.length != 3)
    return;

var options = {
    windowSize: { width: 1024, height: 474 },
    shotSize: { width: 'all', height: 'all' },
    shotOffset: {left: 0, right: 0, top: 0, bottom: 0 },
    takeShotOnCallback: true,
    onLoadFinished: function() { window.callPhantom('takeShot'); },
    defaultWhiteBackground: true,
    streamType: "png",
    renderDelay: 5000,
    userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36'
};

try {
    var renderStream = webshot('http://localhost/share_datalet/' + process.argv[2], options);
    var file = fs.createWriteStream('../static/images/datalet/datalet_' + process.argv[2] + '.png', {encoding: 'binary'});

    renderStream.on('data', function (data) {
        file.write(data.toString('binary'), 'binary');
    });

}catch(e) {}
