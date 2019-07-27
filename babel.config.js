module.exports = function (api) {
    api.cache(true);

    const presets = [["@babel/preset-env", {
        useBuiltIns: "entry",
        corejs: 3,
        //debug: true,
        targets: {
            "chrome": "50",
            "safari": "9",
            "firefox": "50",
            "esmodules": true
        }}]];
    const plugins = ["@babel/plugin-proposal-object-rest-spread", "@babel/plugin-transform-destructuring", "@babel/plugin-transform-spread" ];
    return {
        presets,
        plugins
    };
}