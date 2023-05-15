@font-face {
    font-family: "<%= fontName %>";
    font-display: swap;
    src: url('<%= fontPath %><%= fontName %>.eot<%= cacheBusterQueryString %>');
    src: url('<%= fontPath %><%= fontName %>.eot?<%= cacheBuster %>#iefix') format('eot'),
        url('<%= fontPath %><%= fontName %>.woff2<%= cacheBusterQueryString %>') format('woff2'),
        url('<%= fontPath %><%= fontName %>.woff<%= cacheBusterQueryString %>') format('woff'),
        url('<%= fontPath %><%= fontName %>.ttf<%= cacheBusterQueryString %>') format('truetype'),
        url('<%= fontPath %><%= fontName %>.svg<%= cacheBusterQueryString %>#<%= fontName %>') format('svg');
}

@function wicon-char($filename) {
    $char: "";
<% _.each(glyphs, function(glyph) { %>
    @if $filename == <%= glyph.fileName %> {
        $char: "\<%= glyph.codePoint %>";
    }<% }); %>

    @return $char;
}

$wicons:(
    <% _.each(glyphs, function(glyph) { %>
    wicon-<%= glyph.fileName %> : "\<%= glyph.codePoint %>",
    <% }); %>
);
