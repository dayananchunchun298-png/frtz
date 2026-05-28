import React, { useCallback, useEffect, useState } from 'react';
import { NavigationContainer, DefaultTheme } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { StatusBar } from 'expo-status-bar';
import {
  Alert,
  FlatList,
  Image,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { getToken, logout, apiRequest, login, register, type Product, type SyncData } from './src/api';
import { theme } from './src/theme';
import { Screen, Title, Subtitle, Card, Input, Button, ErrorBanner } from './src/components/Ui';

const navTheme = {
  ...DefaultTheme,
  colors: {
    ...DefaultTheme.colors,
    background: theme.bg,
    card: theme.card,
    text: theme.text,
    border: theme.border,
    primary: theme.violet,
  },
};

const Stack = createNativeStackNavigator();
const Tabs = createBottomTabNavigator();

function LoginScreen({ onLoggedIn }: { onLoggedIn: () => void }) {
  const [email, setEmail] = useState('customer@pawcare.local');
  const [password, setPassword] = useState('Customer@12345');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const [mode, setMode] = useState<'login' | 'register'>('login');

  const submit = async () => {
    setError('');
    setLoading(true);
    const result = mode === 'login' ? await login(email.trim(), password) : await register(email.trim(), password);
    setLoading(false);

    if (!result.ok) {
      setError(result.error.message);
      return;
    }

    if (mode === 'register') {
      Alert.alert('Account created', 'Verify your email, then log in.');
      setMode('login');
      return;
    }

    onLoggedIn();
  };

  return (
    <Screen>
      <Title>FRTZ PawCare</Title>
      <Subtitle>Connected to frtz-production.up.railway.app</Subtitle>
      {error ? <ErrorBanner message={error} /> : null}
      <Input placeholder="Email" autoCapitalize="none" keyboardType="email-address" value={email} onChangeText={setEmail} />
      <Input placeholder="Password" secureTextEntry value={password} onChangeText={setPassword} />
      <Button label={mode === 'login' ? 'Log in' : 'Register'} onPress={submit} loading={loading} />
      <Button
        label={mode === 'login' ? 'Create account' : 'Back to login'}
        variant="ghost"
        onPress={() => setMode(mode === 'login' ? 'register' : 'login')}
      />
      <Text style={styles.hint}>Demo: customer@pawcare.local / Customer@12345</Text>
    </Screen>
  );
}

function useSync() {
  const [data, setData] = useState<SyncData | null>(null);
  const [error, setError] = useState('');
  const [refreshing, setRefreshing] = useState(false);

  const refresh = useCallback(async () => {
    setRefreshing(true);
    const res = await apiRequest<SyncData>('/api/mobile/sync');
    setRefreshing(false);
    if (res.success) {
      setData(res.data);
      setError('');
    } else {
      setError(res.error.message);
    }
  }, []);

  useEffect(() => {
    void refresh();
  }, [refresh]);

  return { data, error, refreshing, refresh };
}

function ShopScreen() {
  const { data, error, refreshing, refresh } = useSync();
  const [cart, setCart] = useState<Record<number, number>>({});
  const [msg, setMsg] = useState('');

  const add = (id: number) => setCart((c) => ({ ...c, [id]: (c[id] ?? 0) + 1 }));

  const checkout = async () => {
    setMsg('');
    const items = Object.entries(cart).map(([productId, quantity]) => ({
      productId: Number(productId),
      quantity,
    }));
    if (items.length === 0) {
      setMsg('Add products to cart first.');
      return;
    }
    const res = await apiRequest('/api/mobile/orders', { method: 'POST', body: { items } });
    if (res.success) {
      setCart({});
      setMsg('Order placed — visible on web profile.');
      await refresh();
    } else {
      setMsg(res.error.message);
    }
  };

  return (
    <Screen>
      <FlatList
        data={data?.products ?? []}
        keyExtractor={(p) => String(p.id)}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={refresh} tintColor={theme.violet} />}
        ListHeaderComponent={
          <>
            <Title>Shop</Title>
            <Subtitle>Products from shared database</Subtitle>
            {error ? <ErrorBanner message={error} /> : null}
            {msg ? <Text style={styles.msg}>{msg}</Text> : null}
            <Button label="Checkout" onPress={checkout} variant="accent" />
          </>
        }
        renderItem={({ item }: { item: Product }) => (
          <Card>
            {item.image ? (
              <Image source={{ uri: item.image }} style={styles.productImage} resizeMode="cover" />
            ) : null}
            <Text style={styles.itemTitle}>{item.name}</Text>
            <Text style={styles.itemMeta}>${item.price} · stock {item.stock}</Text>
            <Button label="Add to cart" onPress={() => add(item.id)} />
            {cart[item.id] ? <Text style={styles.cartQty}>In cart: {cart[item.id]}</Text> : null}
          </Card>
        )}
      />
    </Screen>
  );
}

function AppointmentsScreen() {
  const { data, error, refreshing, refresh } = useSync();
  const [name, setName] = useState('Buddy');
  const [petType, setPetType] = useState('dog');
  const [msg, setMsg] = useState('');

  const book = async () => {
    setMsg('');
    const date = new Date();
    date.setDate(date.getDate() + 7);
    const res = await apiRequest('/api/mobile/appointments', {
      method: 'POST',
      body: { name, petType, appointmentDate: date.toISOString() },
    });
    if (res.success) {
      setMsg('Booked — shows on web Appointments.');
      await refresh();
    } else {
      setMsg(res.error.message);
    }
  };

  return (
    <Screen>
      <ScrollView refreshControl={<RefreshControl refreshing={refreshing} onRefresh={refresh} tintColor={theme.violet} />}>
        <Title>Appointments</Title>
        <Subtitle>Bookings sync with web dashboard</Subtitle>
        {error ? <ErrorBanner message={error} /> : null}
        <Card>
          <Input placeholder="Pet name" value={name} onChangeText={setName} />
          <Input placeholder="Pet type (dog, cat…)" value={petType} onChangeText={setPetType} />
          <Button label="Book next week" onPress={book} variant="accent" />
          {msg ? <Text style={styles.msg}>{msg}</Text> : null}
        </Card>
        {(data?.appointments.items ?? []).map((a) => (
          <Card key={a.id}>
            <Text style={styles.itemTitle}>{a.name}</Text>
            <Text style={styles.itemMeta}>{a.petType} · {new Date(a.appointmentDate).toLocaleString()}</Text>
          </Card>
        ))}
      </ScrollView>
    </Screen>
  );
}

function OrdersScreen() {
  const { data, error, refreshing, refresh } = useSync();

  const pay = async (orderId: number) => {
    const res = await apiRequest(`/api/mobile/orders/${orderId}/payments`, { method: 'POST', body: { method: 'card' } });
    if (res.success) {
      Alert.alert('Paid', 'Order updated on web and API.');
      await refresh();
    } else {
      Alert.alert('Payment failed', res.error.message);
    }
  };

  return (
    <Screen>
      <FlatList
        data={data?.orders.items ?? []}
        keyExtractor={(o) => String(o.id)}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={refresh} tintColor={theme.violet} />}
        ListHeaderComponent={
          <>
            <Title>Orders</Title>
            <Subtitle>Same records as /profile on web</Subtitle>
            {error ? <ErrorBanner message={error} /> : null}
          </>
        }
        ListEmptyComponent={<Text style={styles.hint}>No orders yet — buy from Shop tab.</Text>}
        renderItem={({ item }) => (
          <Card>
            <Text style={styles.itemTitle}>Order #{item.id}</Text>
            <Text style={styles.itemMeta}>{item.status} · ${item.total}</Text>
            {item.status === 'pending' ? (
              <Button label="Pay (stub)" onPress={() => pay(item.id)} variant="accent" />
            ) : null}
          </Card>
        )}
      />
    </Screen>
  );
}

function ProfileScreen({ onLogout }: { onLogout: () => void }) {
  const { data, error, refreshing, refresh } = useSync();

  return (
    <Screen>
      <ScrollView refreshControl={<RefreshControl refreshing={refreshing} onRefresh={refresh} tintColor={theme.violet} />}>
        <Title>Profile</Title>
        <Subtitle>API: https://frtz-production.up.railway.app</Subtitle>
        {error ? <ErrorBanner message={error} /> : null}
        {data?.user ? (
          <Card>
            <Text style={styles.itemTitle}>{data.user.email}</Text>
            <Text style={styles.itemMeta}>Verified: {data.user.isVerified ? 'Yes' : 'No'}</Text>
            <Text style={styles.itemMeta}>Last sync: {new Date(data.syncedAt).toLocaleString()}</Text>
          </Card>
        ) : null}
        <Button label="Log out" onPress={onLogout} variant="ghost" />
      </ScrollView>
    </Screen>
  );
}

function MainTabs({ onLogout }: { onLogout: () => void }) {
  return (
    <Tabs.Navigator
      screenOptions={{
        headerStyle: { backgroundColor: theme.bgMid },
        headerTintColor: theme.text,
        tabBarStyle: { backgroundColor: theme.bgMid, borderTopColor: theme.border },
        tabBarActiveTintColor: theme.violet,
        tabBarInactiveTintColor: theme.textMuted,
      }}
    >
      <Tabs.Screen name="Shop" component={ShopScreen} />
      <Tabs.Screen name="Book" component={AppointmentsScreen} />
      <Tabs.Screen name="Orders" component={OrdersScreen} />
      <Tabs.Screen name="Profile" children={() => <ProfileScreen onLogout={onLogout} />} />
    </Tabs.Navigator>
  );
}

export default function App() {
  const [ready, setReady] = useState(false);
  const [authed, setAuthed] = useState(false);

  useEffect(() => {
    void (async () => {
      const token = await getToken();
      setAuthed(!!token);
      setReady(true);
    })();
  }, []);

  const handleLogout = async () => {
    await logout();
    setAuthed(false);
  };

  if (!ready) {
    return <View style={styles.boot} />;
  }

  return (
    <NavigationContainer theme={navTheme}>
      <StatusBar style="light" />
      <Stack.Navigator screenOptions={{ headerShown: false }}>
        {authed ? (
          <Stack.Screen name="Main">
            {() => <MainTabs onLogout={handleLogout} />}
          </Stack.Screen>
        ) : (
          <Stack.Screen name="Login">
            {() => <LoginScreen onLoggedIn={() => setAuthed(true)} />}
          </Stack.Screen>
        )}
      </Stack.Navigator>
    </NavigationContainer>
  );
}

const styles = StyleSheet.create({
  boot: { flex: 1, backgroundColor: theme.bg },
  hint: { color: theme.textMuted, fontSize: 12, marginTop: 16, textAlign: 'center' },
  msg: { color: theme.success, marginBottom: 12 },
  itemTitle: { color: theme.text, fontSize: 17, fontWeight: '600' },
  itemMeta: { color: theme.textMuted, marginTop: 4, marginBottom: 8 },
  cartQty: { color: theme.violet, marginTop: 8 },
  productImage: {
    width: '100%',
    height: 140,
    borderRadius: 10,
    marginBottom: 10,
    backgroundColor: theme.bgMid,
  },
});
