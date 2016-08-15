<p><strong>Hello!</strong></p>

<p>We're sorry to hear you've lost your password. Click here to select a new one: <a href="{{ $link = url('password/reset', $token).'?email='.urlencode($user->getEmailForPasswordReset()) }}"> {{ $link }} </a></p>

<p>Sincerely,<br />the Staff</p>
