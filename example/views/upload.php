<!DOCTYPE html>

<form action="/upload" method="POST" enctype="multipart/form-data">
  <input type="text" name="message" value="<?php echo $request->body['message'] ?>">
  <input type="file" name="file" >
  <p><input type="submit" value="Continue &rarr;"></p>
</form>

<pre>
<?php print_r( $request->body )?>
</pre>