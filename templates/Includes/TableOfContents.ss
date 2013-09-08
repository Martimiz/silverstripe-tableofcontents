<div id="TOCcontainer">
	<a id="toc"></a>
	<div id=\"table-of-contents\">
		<{$TOCHeaderTag} id="TOCTitle">{$TOCTitle}</{$TOCHeaderTag}>
		<ul>
			<% loop $TOCItems %>
				<li class="{$CssClass}"><a href="$Link" class="scroll" title="$Title">$Title</a></li>
			<% end_loop %>
		<ul>
	</div>
</div>

