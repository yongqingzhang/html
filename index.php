<!--creat a form to display different species by gene position-->
<html>
	<head>
		<title>query gene name by gene position</title>
	</head>
	<body>
			<h2>Query gene gene by gene position</h2>
		<!--	<form action="pregSplit.php" method="post">-->
			<form action="/cpbrowser/genelist.php" method="post">
			<table width="400" border="0">
			<tr>
				<td align='right'>species:</td>
				<td>
					<select name="species">
						<option value="Human">Human</option>
						<option value="Mouse">Mouse</option>
						<option value="Pig">Pig</option>
					</select>
				</td>
			</tr>
			<tr>
				<td align='right'>position:</td>
				<td><input type="text" name="geneName"/></td>
			</tr>
			<tr>
				<td></td>
				<td>(chr21:33333-35555)</td>
			</tr>
			<tr>
				<td align='right'><input type="submit" value="Submit"/></td>
				<td></td>
			</tr>
			</table>
			</form>
	</body>
</html>
