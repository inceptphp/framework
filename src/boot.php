<?php //-->

$this
  //register the resolver
  ->register('resolver', sprintf('%s/Package/Resolver', __DIR__))
  //register the event
  ->register('event', sprintf('%s/Package/Event', __DIR__))
  //register the http
  ->register('http', sprintf('%s/Package/Http', __DIR__))
  //register the terminal
  ->register('terminal', sprintf('%s/Package/Terminal', __DIR__))
  //register the PDO
  ->register('pdo', sprintf('%s/Package/PDO', __DIR__))
  //register the config
  ->register('config', sprintf('%s/Package/Config', __DIR__))
  //register the host
  ->register('host', sprintf('%s/Package/Host', __DIR__))
  //register the lang
  ->register('lang', sprintf('%s/Package/Language', __DIR__))
  //register the tz
  ->register('tz', sprintf('%s/Package/Timezone', __DIR__))
  //register the system
  ->register('system', sprintf('%s/Package/System', __DIR__))
  //use one global resolver
  ->setResolverHandler($this('resolver')->getResolverHandler())
  //use one global event emitter
  ->setEventEmitter($this('event')->getEventEmitter());
