<?php return array (
  'App\\Providers\\EventServiceProvider' => 
  array (
    'Illuminate\\Auth\\Events\\Registered' => 
    array (
      0 => 'Illuminate\\Auth\\Listeners\\SendEmailVerificationNotification',
    ),
    'Illuminate\\Auth\\Events\\Attempting' => 
    array (
      0 => 'App\\Listeners\\LogAuthenticationEvents',
    ),
    'Illuminate\\Auth\\Events\\Login' => 
    array (
      0 => 'App\\Listeners\\LogAuthenticationEvents',
    ),
    'Illuminate\\Auth\\Events\\Failed' => 
    array (
      0 => 'App\\Listeners\\LogAuthenticationEvents',
    ),
    'App\\Events\\SwapRequested' => 
    array (
      0 => 'App\\Listeners\\SendSwapRequestNotification',
    ),
    'App\\Events\\SwapApproved' => 
    array (
      0 => 'App\\Listeners\\SendSwapApprovedNotification',
    ),
    'App\\Events\\SwapRejected' => 
    array (
      0 => 'App\\Listeners\\SendSwapRejectedNotification',
    ),
    'App\\Events\\SwapCompleted' => 
    array (
      0 => 'App\\Listeners\\SendSwapCompletedNotification',
    ),
  ),
  'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider' => 
  array (
    'App\\Events\\SwapApproved' => 
    array (
      0 => 'App\\Listeners\\SendSwapApprovedNotification@handle',
    ),
    'App\\Events\\SwapCompleted' => 
    array (
      0 => 'App\\Listeners\\SendSwapCompletedNotification@handle',
    ),
    'App\\Events\\SwapRejected' => 
    array (
      0 => 'App\\Listeners\\SendSwapRejectedNotification@handle',
    ),
    'App\\Events\\SwapRequested' => 
    array (
      0 => 'App\\Listeners\\SendSwapRequestNotification@handle',
    ),
  ),
);