import EditPageTool from './EditPageTool';
import wooyModel from './woodyModel';
import woodyMenu from './woodyMenu';
import woodyPublish from './woodyPublish';

// Construction des outils dans la page d'édition de page
const woodyParent = new EditPageTool('#pageparentdiv');
const woodyCustomMenu = new EditPageTool('#acf-group_5ba8ef4753801');
const woodyCustomMEA = new EditPageTool('#acf-group_5b0d380ce3492');
const woodyLanguages = new EditPageTool('#ml_box');
const woodyModel = new wooyModel('#woody_model_metabox');

// Construction du nouveau Menu & Lien rapide
const Menu = new woodyMenu();

// Construction de la zone de publication
const Publish = new woodyPublish();
