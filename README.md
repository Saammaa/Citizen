# 公民
一款 Typecho 主题，基于 Star Citizen Wiki 与 Matcha。

<img alt="简单预览" src="https://s2.loli.net/2023/11/16/8fHrvOEpbSlnBd3.png" title="简单预览" width="70%"/>

<img alt="简单预览" src="https://s2.loli.net/2023/11/16/ywJ52F9caoPhDkZ.png" title="黑夜模式" width="70%"/>

更多预览与文档请参考 [Saammaa's Plaza](https://saammaa.com/citizen)。

## 须知
内置播放器依赖一并内置的 Meting API。请将主题文件夹中的 `play.php` 移至 Typecho 的**根目录**下，播放器才能够访问该服务，否则无法正常工作。

我们提供了一个自定义页面模板（分类总览，`categories.php`）用以显示所有分类。
## 特性
1. 全局 PJAX；
2. 全局经过大量优化的过渡动效；
3. 内置大小仅为 `3.6KB` 的播放器；
4. 播放器音频波形动态可视化；
5. 标准、健壮。编译后内核 JS 大小仅为 `13.1KB`； 
6. 代码简洁，性能优异，响应迅速；
7. 支持点赞；
8. 支持 AJAX 评论；
9. 支持明暗状态切换；
10. 支持搜索结果高亮；
11. 支持 FancyBox 与 LazyLoad；
12. 支持目录显示；
13. 支持文章/页面描述；
14. 支持文章/页面背景图；
15. 支持基于 Prism 的代码块高亮；
16. 引入大量额外正文文本样式（如：黑幕）；
17. 使用 GoogleFonts 与 Material Icons；
18. 极限节流，核心文件均可从 NPM 加载，页面一顿加载下来，最后发现只有两个请求是自己的；
19. 内置原生块级网格布局样式，开箱即用；
20. 主题仅提供两个配置，~~培养大家的动手能力~~。

## 小控件
👀 以下是一些参考元素：<br><br>
<img src="https://s2.loli.net/2023/11/16/WlRGgU1Qm8LZvET.png" alt="播放器" height="128">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="https://s2.loli.net/2023/11/16/E4zxuDPfa6yntMd.png" title="表情" height="128"><br>
<small><i>是的，这个皮肤支持表情，仅需另外参考并引入我们 NPM 包中的 emoji/superBuild.css 即可。</i></small>
<hr>
<img src="https://s2.loli.net/2023/11/16/9ApGPw2D1Ikgv6f.png" alt="播放器" height="255"><br><br>
<span style="font-size:large"><b><i>哎我实在是懒得贴了就这样吧</i></b></span>
