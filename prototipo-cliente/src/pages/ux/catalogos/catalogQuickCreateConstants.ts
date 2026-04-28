import type { CatalogTabId } from './catalogResourceMeta'
import { CATALOG_RESOURCE_META } from './catalogResourceMeta'

/** Creación rápida desde otro formulario solo si el CRUD destino tiene como máximo esta cantidad de campos. */
export const MAX_QUICK_CREATE_TARGET_FIELDS = 3

export function canQuickCreateIntoCatalog(tab: CatalogTabId): boolean {
  return CATALOG_RESOURCE_META[tab].formFields.length <= MAX_QUICK_CREATE_TARGET_FIELDS
}
