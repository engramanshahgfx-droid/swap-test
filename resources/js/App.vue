<template>
  <router-view />
</template>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

:root {
  --primary: #0a2a66;
  --secondary: #0ea5e9;
  --success: #15803d;
  --warning: #c2410c;
  --danger: #b91c1c;
  --surface: #f4f7fb;
  --card: #ffffff;
  --ink: #10203d;
  --muted: #64748b;
  --border: #d9e4f1;
}

body {
  font-family: 'Plus Jakarta Sans', 'Manrope', 'Segoe UI', sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  background:
    radial-gradient(circle at 0% 0%, rgba(14, 165, 233, 0.08), transparent 35%),
    radial-gradient(circle at 100% 100%, rgba(10, 42, 102, 0.08), transparent 28%),
    var(--surface);
  color: var(--ink);
}

#app {
  min-height: 100vh;
}

body.dark {
  --surface: #071120;
  --card: #0f1f37;
  --ink: #dce9fb;
  --muted: #8aa0bf;
  --border: #233a5b;
}
</style>
