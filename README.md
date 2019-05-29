# Jellyfin-NMT

## CSS Stylesheet Notes
- [Limited CSS support](http://files.syabas.com/networkedmediatank/www.networkedmediatank.com/download/docs/NMT_stylesheet_20080118.htm)
- Applying multiple classes to style doesn't work on NMT
    ```css
    .abc, .xyz { margin-left: 20px; }
    ```
- Inheritance seems weird, or just doesn't work.
  - Color from body was not inheriting down to TD elements. Must set for more specific tags.
  - Redefining an attribute (like color) for the same item does not work. First value is kept.
    - example: color will be #8e8e8e.
    ```css
        .indexname { color: #8e8e8e; }
        .indexname { color: #dddddd; }
    ```


