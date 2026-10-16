import "./bootstrap";
import Alpine from "alpinejs";
import receivingAssistant from "./receiving-assistant";

import batchAssistant from "./batch-assistant";

import customerWizard from "./customer-wizard";

window.Alpine = Alpine;
Alpine.data("customerWizard", customerWizard);
Alpine.data("receivingAssistant", receivingAssistant);
Alpine.data("batchAssistant", batchAssistant);
Alpine.start();

import { initializeAsyncTables } from './async-table';
initializeAsyncTables();

import { initializeDeleteConfirmation } from './delete-confirmation';
initializeDeleteConfirmation();

import { initializeModalLifecycle } from './modal-lifecycle';
initializeModalLifecycle();

import { initializeFormSubmissions } from './form-submission';
initializeFormSubmissions();
