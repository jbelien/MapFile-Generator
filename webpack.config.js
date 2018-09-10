const path = require("path");

module.exports = {
    entry: {
        mg: "./resources/javascript/main.js"
    },
    output: {
        filename: "[name].min.js",
        path: path.resolve(__dirname, "public/js")
    }
};
