<!DOCTYPE html>
<body id="form" onload="">
  <form action="/" method="post" accept-charset="utf-8">
    <input type="text" name="hello">
    <p><input type="submit" value="Continue &rarr;"></p>
  </form>
  
<pre>
<?php print_r( $request->body ) ?>
</pre>
</body>