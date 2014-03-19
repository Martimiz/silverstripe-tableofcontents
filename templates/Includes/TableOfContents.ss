<a name="toc" id="toc"></a>
<div id="TOCcontainer">
	<div id="table-of-contents">
		<{$TOCHeaderTag} id="TOCTitle">{$TOCTitle}</{$TOCHeaderTag}>
		<ul>
		<% loop $TOCItems %>
			<li class="{$CssClass} {$FirstLast}"><a href="$Link" class="scroll" title="$Title">$Title</a></li>
		<% end_loop %>
		</ul>
	</div>
</div>

