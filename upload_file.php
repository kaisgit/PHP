<html>
<head>
<style type="text/css">
.btn {
    background-color:#007dc1;
    cursor:pointer;
    color:#ffffff;
    font-family:Arial,Helvetica;
    font-size:15px;
    text-decoration:none;
    margin: 15px 15px;
	padding: 10px 10px;
	border: 0;
	position: relative;
}
</style>
</head>
<body>

<form method="post" enctype="multipart/form-data" action="confirm_to_upload.php" >
    <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
    <input class="btn" type="file" name="filename" />
    <input class="btn" type="submit" value="Click to Upload" name="Upload" />
</form>

<!--
<form method="post" enctype="multipart/form-data" action="deployment_kai.php" >
    <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
    <input class="btn" type="file" name="filename" />
    <input class="btn" type="submit" value="Click to upload a file" name="Upload" />
</form>
-->

</body>
</html>
