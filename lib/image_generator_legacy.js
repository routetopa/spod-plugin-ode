const webshot = require("webshot");
const fs = require("fs");
const mysql = require('mysql');

var mysql_con = mysql.createConnection({
    host: "localhost",
    user: "root",
    password: "is15rdc"
});

var browser_options = {
    windowSize: { width: 948, height: 474 },
    shotSize: { width: 'all', height: 'all' },
    shotOffset: {left: 0, right: 0, top: 0, bottom: 0 },
    takeShotOnCallback: true,
    onLoadFinished: function() { window.callPhantom('takeShot'); },
    defaultWhiteBackground: true,
    streamType: "png",
    renderDelay: 10000,
    userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36'
};


mysql_con.connect(function(err) {

    if (err) throw err;
    console.log("Connected!");

    mysql_con.query("SELECT * FROM spod.ow_ode_datalet order by id desc", function (err, results, fields)  {
        if (err) throw err;
        create_image_rec(results, 0);
    });

});

function create_image_rec(results, i)
{
    try {

        if(i === results.length)
            process.exit();

        webshot('http://localhost/share_datalet/' + results[i].id, '../datalet_images/datalet_' + results[i].id + '.png', browser_options, function (err) {
           console.log('saved ' + results[i].id);
           create_image_rec(results, ++i);
        });

    } catch (e) {
        console.log("Errore");
    }
}