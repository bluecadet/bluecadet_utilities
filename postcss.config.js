module.exports = {
  parser: "postcss-scss",
  plugins: [
    require("postcss-easy-import")({        // Enables globs in @import. See style.css.
      prefix: false,
      skipDuplicates: false,
      warnOnEmpty: false,
    }),
    require("postcss-advanced-variables"),  // Sass-style @ vars, looping, and @import
    require("postcss-custom-media"),        // Custom reusable media queries
    require("postcss-nested"),              // Sass-style rule nesting
    require("postcss-preset-env") ({        // Automatically polyfill modern CSS features
      features: {
        "nesting-rules": false, // disables CSS nesting in favor of Sass-style nesting (postcss-nested)
      },
    }),
    require("postcss-pxtorem"),             // Converts units to rems (defaults to only font properties @ 16px base size)
    require("postcss-assets"),              // Filename resolver for images
    require('postcss-discard-comments'),    // Removes all comments (/* */)
  ],
};