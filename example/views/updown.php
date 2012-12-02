<!DOCTYPE html>

<form action="/updown" method="POST" enctype="multipart/form-data">
  <input type="text" name="filename" value="<?php echo $request->body['filename'] ?>">
  <input type="file" name="file" >
  <p><input type="submit" value="Continue &rarr;"></p>
</form>

<pre>
<?php print_r( $request->body )?>
</pre>