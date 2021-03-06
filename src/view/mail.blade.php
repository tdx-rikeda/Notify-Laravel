<!DOCTYPE HTML>
<html lang="ja">
<head>
  <meta charset="utf-8"/>
  <title>Laravelでメール送信</title>
</head>
<body>
  @if(isset($text))

      @foreach ($text as $row)
        <p>
        {{ $row }}
        </p>
      @endforeach
  @else
    <p style="font-size: 14px; font-style: italic;"><b><u>{{ $errorName }}</u></b></p>
    <p><b>{{ $errorTitle }}</b></p>
    <p>
    @foreach ($trace as $row)
      {{$row}}<br />
    @endforeach
      </p>
  @endif

  <div class="fields">
    @if (isset($userAgent))
      <p><b>HTTP_USER_AGENT :</b> {{ $userAgent }}</p>
    @endif
    @if (isset($requestUri))
    <p><b>REQUEST_URI :</b> {{ $requestUri}}</p>
    @endif
  </div>
</body>
</html>