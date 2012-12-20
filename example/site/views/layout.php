<!DOCTYPE html>
<head>
  <title>Hello</title>
  <link rel="stylesheet" href="/style.css" type="text/css" charset="utf-8">
</head>

<body>
  <?php echo $this->content() ?>
  <hr>
  <?php if( $request->user ): ?>  
    <p>You are logged in as <?php echo $request->user ?>.</p>
  <?php else: ?>
    <p>You are not logged in. <a href="/login">Log in here</a>.</p>
  <?php endif; ?>
</body>