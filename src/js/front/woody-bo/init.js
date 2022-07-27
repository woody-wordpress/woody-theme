console.log('Woody-BO-Front');

import Account from './Objects/Account';
import DevTools from './Objects/DevTools';
import Dashboard from './Objects/Dashboard';

const woodyAccount = new Account('#wp-admin-bar-my-account');
const woodyDevTools = new DevTools('#wp-admin-bar-woody-dev-tools');
const woodyDashboard = new Dashboard('#wp-admin-bar-root-default');
