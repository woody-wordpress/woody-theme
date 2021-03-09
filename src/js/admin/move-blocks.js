
import $ from 'jquery';

document.querySelectorAll('.acf-fc-layout-controls').forEach((element, index) => {
  let container = document.createElement('div');
  container.classList.add('move-block-container');
  let button = document.createElement('a');
  button.className = "acf-icon -duplicate small light acf-js-tooltip move-button";
  button.href = "#";
  button.dataset.name = 'move-layout';
  button.title = 'Déplacer le bloc';

  let tooltip = document.createElement('div');
  tooltip.classList.add('move-block-tooltip');

  tooltip.innerHTML = `<label for="move-block-select-${index}">Déplacer vers la section: </label>`

  let tooltipSelect = document.createElement('select');
  tooltipSelect.classList.add('move-block-select');
  tooltipSelect.id = 'move-block-select-' + index;
  tooltip.append(tooltipSelect);
  let tooltipOpen = false;

  tooltipSelect.addEventListener('change', e => { moveBlock($(element), tooltipSelect.value, tooltip); });
  window.addEventListener('click', e => {   
    if (!tooltip.contains(e.target) && tooltipOpen) {
      if (button.contains(e.target)) {
        return;
      }
      tooltipOpen = toggleMoveTooltip(tooltipOpen, element.parentElement.firstElementChild.name, tooltip, tooltipSelect);
    }
  });
  button.addEventListener('click', e => { tooltipOpen = toggleMoveTooltip(tooltipOpen, element.parentElement.firstElementChild.name, tooltip, tooltipSelect); });

  container.append(button);
  container.append(tooltip);
  element.prepend(container);
});

const moveBlock = (element, section, tooltip = null) => {

  // Close tooltip
  if (tooltip) $(tooltip).removeClass('open');

  // Select section
  const sections = document.querySelectorAll('div[data-name="section_content"]');
  const sectionIndex = Math.min(Math.max(0, section), sections.length - 1); 

  const values = sections[sectionIndex].querySelectorAll('.acf-flexible-content > .values');
  const blocks = values[values.length - 1];
  const last = blocks.lastElementChild;

  $(blocks).closest('.acf-flexible-content').removeClass('-empty');

  // Get field keys
  const prevKey = element.parent().data('id');
  const newKey = acf.uniqid('layout_');

  // Duplicate field and remove original
  let layout = acf.duplicate({
    $el: $(element).parent(),
    search: prevKey,
    replace: newKey,
    append: ($el, $el2) => {
      if (last) $(last).after($el2);
      else $(blocks).append($el2);
    }
  });
  acf.remove($(element).parent());

  let fieldKey = `acf[field_5afd2c6916ecb][row-${sectionIndex}][field_5b043f0525968][row-${values.length}][acf_fc_layout]`;

  // Bind new move button event
  const layoutMoveButton = layout.find('.move-button');
  const layoutMoveTooltip = layout.find('.move-block-tooltip');
  const layoutMoveSelect = layout.find('.move-block-select');
  const layoutMoveContainer = layout.find('.move-block-container');

  let tooltipOpen = false;

  layoutMoveSelect.on('change', e => { moveBlock(layoutMoveContainer.parent(), layoutMoveSelect[0].value, layoutMoveTooltip[0]); });
  layoutMoveButton.on('click', e => { tooltipOpen = toggleMoveTooltip(tooltipOpen, fieldKey, layoutMoveTooltip[0], layoutMoveSelect[0]); });

  window.addEventListener('click', e => {   
    if (!layoutMoveTooltip[0].contains(e.target) && tooltipOpen) {
      if (layoutMoveButton[0].contains(e.target)) {
        return;
      }
      tooltipOpen = toggleMoveTooltip(tooltipOpen, layoutMoveContainer.parent().parent()[0].firstElementChild.name, layoutMoveTooltip[0], layoutMoveSelect[0]);
    }
  });

  // layoutMoveButton.on('click', e => { moveBlock(e, layoutMoveButton.parent()) });

  // Overwrite name with correct field key
  layout.children().first().attr("name", fieldKey);
}

const toggleMoveTooltip = (open, key, tooltip, select) => {
  select.innerHTML = "";
  const sectionLength = document.querySelectorAll('div[data-name="section_content"]').length - 1;
  let isInSection = -1;

  for(let i = 0; i < sectionLength; ++i) {
    if (key.startsWith(`acf[field_5afd2c6916ecb][row-${i}]`)) isInSection = i;
    select.innerHTML += `<option value="${i}">${i + 1}</option>`;
  }
  select.value = (isInSection >= 0) ? isInSection : 0;
  tooltip.classList.toggle('open');
  return !open;
}