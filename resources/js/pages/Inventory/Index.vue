<script setup lang="ts">
import { ref, onMounted } from 'vue';

// Tabs
const tab = ref<'ingredients'|'dishes'|'grn'>('ingredients');

// Utils
async function j(url:string, init?:RequestInit){ const r=await fetch(url,{headers:{'Content-Type':'application/json','Accept':'application/json'}, ...init}); if(!r.ok) throw new Error(await r.text()); return r.json(); }

// INGREDIENTS
const ingredients = ref<any[]>([]);
const ing = ref<{name:string; unit_name:string; price_cents:number}>({name:'', unit_name:'', price_cents:0});
async function loadIngredients(){ ingredients.value = await j('/api/inventory/ingredients'); }
async function createIngredient(){ await j('/api/inventory/ingredients',{method:'POST', body:JSON.stringify(ing.value)}); ing.value={name:'',unit_name:'',price_cents:0}; await loadIngredients(); }
async function updateIngredient(row:any){ await j(`/api/inventory/ingredients/${row.id}`,{method:'PUT', body:JSON.stringify(row)}); await loadIngredients(); }
async function deleteIngredient(id:number){ if(!confirm('Delete ingredient?')) return; await j(`/api/inventory/ingredients/${id}`,{method:'DELETE'}); await loadIngredients(); }

// DISHES
const dishes = ref<any[]>([]);
const dish = ref<{name:string; price_cents:number}>({name:'', price_cents:0});
async function loadDishes(){ dishes.value = await j('/api/inventory/dishes'); }
async function createDish(){ await j('/api/inventory/dishes',{method:'POST', body:JSON.stringify(dish.value)}); dish.value={name:'',price_cents:0}; await loadDishes(); }

// VARIANTS + COMPONENTS
const selectedDish = ref<any|null>(null);
const variants = ref<any[]>([]);
const components = ref<any[]>([]);
const newVariant = ref<{name:string; is_default:boolean}>({name:'Regular', is_default:true});
const compForm = ref<{ingredient_product_id:number|null; qty_per_unit:number; unit_name:string}>({ingredient_product_id:null, qty_per_unit:1, unit_name:''});

async function pickDish(d:any){ selectedDish.value=d; await loadVariants(); components.value=[]; }
async function loadVariants(){ if(!selectedDish.value) return; variants.value = await j(`/api/inventory/dishes/${selectedDish.value.id}/variants`); }
async function addVariant(){ if(!selectedDish.value) return; await j(`/api/inventory/dishes/${selectedDish.value.id}/variants`,{method:'POST', body:JSON.stringify(newVariant.value)}); newVariant.value={name:'Regular', is_default:false}; await loadVariants(); }
async function makeDefault(v:any){ if(!selectedDish.value) return; await j(`/api/inventory/dishes/${selectedDish.value.id}/variants/${v.id}`,{method:'PUT', body:JSON.stringify({name:v.name, is_default:true})}); await loadVariants(); }
async function deleteVariant(v:any){ if(!selectedDish.value) return; if(!confirm('Delete variant?')) return; await j(`/api/inventory/dishes/${selectedDish.value.id}/variants/${v.id}`,{method:'DELETE'}); await loadVariants(); components.value=[]; }

const selectedVariant = ref<any|null>(null);
async function pickVariant(v:any){ selectedVariant.value=v; components.value = await j(`/api/inventory/variants/${v.id}/components`); }
async function upsertComponent(){ if(!selectedVariant.value || compForm.value.ingredient_product_id==null) return;
  await j(`/api/inventory/variants/${selectedVariant.value.id}/components`,{method:'POST', body:JSON.stringify(compForm.value)});
  components.value = await j(`/api/inventory/variants/${selectedVariant.value.id}/components`);
  compForm.value={ingredient_product_id:null, qty_per_unit:1, unit_name:''};
}
async function deleteComponent(row:any){ if(!selectedVariant.value) return;
  await j(`/api/inventory/variants/${selectedVariant.value.id}/components/${row.id}`,{method:'DELETE'});
  components.value = await j(`/api/inventory/variants/${selectedVariant.value.id}/components`);
}

// GRN (Receive Stock)
const grn = ref<{location_id:number; external_ref:string; lines:{product_id:number; qty:number; unit_cost_cents:number}[]}>({
  location_id:1, external_ref:'', lines:[{product_id:0, qty:0, unit_cost_cents:0}]
});
async function addLine(){ grn.value.lines.push({product_id:0, qty:0, unit_cost_cents:0}); }
function removeLine(i:number){ grn.value.lines.splice(i,1); }
async function postGRN(){
  // only ingredients should be received typically
  await j('/api/grn',{method:'POST', body:JSON.stringify(grn.value)});
  alert('Stock received');
  grn.value={location_id:1, external_ref:'', lines:[{product_id:0, qty:0, unit_cost_cents:0}]};
}

onMounted(async () => {
  await Promise.all([loadIngredients(), loadDishes()]);
});
</script>

<template>
  <div class="p-6 space-y-4">
    <div class="flex gap-2">
      <button class="px-3 py-2 rounded-lg" :class="tab==='ingredients'?'bg-orange-600 text-white':'bg-white'" @click="tab='ingredients'">Ingredients</button>
      <button class="px-3 py-2 rounded-lg" :class="tab==='dishes'?'bg-orange-600 text-white':'bg-white'" @click="tab='dishes'">Dishes & Recipes</button>
      <button class="px-3 py-2 rounded-lg" :class="tab==='grn'?'bg-orange-600 text-white':'bg-white'" @click="tab='grn'">Receive Stock (GRN)</button>
    </div>

    <!-- INGREDIENTS TAB -->
    <div v-if="tab==='ingredients'" class="glass-effect rounded-xl p-4">
      <h2 class="font-semibold mb-3">Ingredients</h2>
      <form class="grid grid-cols-4 gap-2 mb-4" @submit.prevent="createIngredient">
        <input v-model="ing.name" class="border rounded px-2 py-2" placeholder="Name (e.g., Beef)"/>
        <input v-model="ing.unit_name" class="border rounded px-2 py-2" placeholder="Unit (e.g., lb, kg, pcs)"/>
        <input v-model.number="ing.price_cents" type="number" class="border rounded px-2 py-2" placeholder="Price cents (optional)"/>
        <button class="bg-black text-white rounded px-3">Add</button>
      </form>

      <table class="w-full text-sm">
        <thead><tr class="text-left border-b"><th>Name</th><th>Unit</th><th>Price</th><th></th></tr></thead>
        <tbody>
          <tr v-for="row in ingredients" :key="row.id" class="border-b">
            <td><input v-model="row.name" class="border rounded px-2 py-1"/></td>
            <td><input v-model="row.unit_name" class="border rounded px-2 py-1"/></td>
            <td><input v-model.number="row.price_cents" type="number" class="border rounded px-2 py-1"/></td>
            <td class="text-right">
              <button class="px-2 py-1 bg-gray-800 text-white rounded mr-2" @click="updateIngredient(row)">Save</button>
              <button class="px-2 py-1 bg-red-600 text-white rounded" @click="deleteIngredient(row.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- DISHES & RECIPES TAB -->
    <div v-if="tab==='dishes'" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Dishes -->
      <div class="glass-effect rounded-xl p-4">
        <h2 class="font-semibold mb-3">Dishes</h2>
        <form class="grid grid-cols-3 gap-2 mb-3" @submit.prevent="createDish">
          <input v-model="dish.name" class="border rounded px-2 py-2" placeholder="Dish name"/>
          <input v-model.number="dish.price_cents" type="number" class="border rounded px-2 py-2" placeholder="Price (cents)"/>
          <button class="bg-black text-white rounded px-3">Add</button>
        </form>
        <ul class="space-y-2">
          <li v-for="d in dishes" :key="d.id" class="border rounded p-2 flex items-center justify-between">
            <div>
              <div class="font-medium">{{ d.name }}</div>
              <div class="text-xs opacity-70">Price: {{ (d.price_cents/100).toFixed(2) }}</div>
            </div>
            <div class="flex gap-2">
              <button class="px-2 py-1 bg-blue-600 text-white rounded" @click="pickDish(d)">Recipes</button>
            </div>
          </li>
        </ul>
      </div>

      <!-- Variants -->
      <div class="glass-effect rounded-xl p-4" v-if="selectedDish">
        <h2 class="font-semibold mb-3">Variants — {{ selectedDish.name }}</h2>
        <form class="grid grid-cols-3 gap-2 mb-3" @submit.prevent="addVariant">
          <input v-model="newVariant.name" class="border rounded px-2 py-2" placeholder="Variant name (e.g., Regular)"/>
          <label class="flex items-center gap-2"><input type="checkbox" v-model="newVariant.is_default"/> Default</label>
          <button class="bg-black text-white rounded px-3">Add</button>
        </form>
        <ul class="space-y-2">
          <li v-for="v in variants" :key="v.id" class="border rounded p-2 flex items-center justify-between">
            <div>
              <div class="font-medium">{{ v.name }} <span v-if="v.is_default" class="text-xs bg-green-600 text-white px-2 py-0.5 rounded ml-1">default</span></div>
            </div>
            <div class="flex gap-2">
              <button class="px-2 py-1 bg-gray-700 text-white rounded" @click="pickVariant(v)">Edit BOM</button>
              <button class="px-2 py-1 bg-amber-600 text-white rounded" @click="makeDefault(v)">Make Default</button>
              <button class="px-2 py-1 bg-red-600 text-white rounded" @click="deleteVariant(v)">Delete</button>
            </div>
          </li>
        </ul>
      </div>

      <!-- Components (BOM) -->
      <div class="glass-effect rounded-xl p-4" v-if="selectedVariant">
        <h2 class="font-semibold mb-3">Recipe Components — {{ selectedVariant.name }}</h2>
        <form class="grid grid-cols-4 gap-2 mb-3" @submit.prevent="upsertComponent">
          <select v-model.number="compForm.ingredient_product_id" class="border rounded px-2 py-2">
            <option :value="null" disabled>Select ingredient</option>
            <option v-for="i in ingredients" :key="i.id" :value="i.id">{{ i.name }} ({{ i.unit_name ?? 'unit' }})</option>
          </select>
          <input v-model.number="compForm.qty_per_unit" type="number" step="0.0001" class="border rounded px-2 py-2" placeholder="Qty per dish"/>
          <input v-model="compForm.unit_name" class="border rounded px-2 py-2" placeholder="Unit (optional)"/>
          <button class="bg-black text-white rounded px-3">Add/Update</button>
        </form>

        <table class="w-full text-sm">
          <thead><tr class="text-left border-b"><th>Ingredient</th><th>Qty per dish</th><th>Unit</th><th></th></tr></thead>
          <tbody>
            <tr v-for="row in components" :key="row.id" class="border-b">
              <td>{{ row.ingredient_name }}</td>
              <td>{{ row.qty_per_unit }}</td>
              <td>{{ row.unit_name || row.default_unit || '' }}</td>
              <td class="text-right">
                <button class="px-2 py-1 bg-red-600 text-white rounded" @click="deleteComponent(row)">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- GRN TAB -->
    <div v-if="tab==='grn'" class="glass-effect rounded-xl p-4">
      <h2 class="font-semibold mb-3">Receive Stock (Ingredients)</h2>
      <div class="grid grid-cols-3 gap-2 mb-3">
        <input v-model="grn.external_ref" class="border rounded px-2 py-2" placeholder="External ref (PO#)"/>
        <input v-model.number="grn.location_id" type="number" class="border rounded px-2 py-2" placeholder="Location ID"/>
      </div>

      <div class="space-y-2">
        <div v-for="(ln,i) in grn.lines" :key="i" class="grid grid-cols-4 gap-2">
          <select v-model.number="ln.product_id" class="border rounded px-2 py-2">
            <option :value="0" disabled>Select ingredient</option>
            <option v-for="ing in ingredients" :key="ing.id" :value="ing.id">{{ ing.name }} ({{ ing.unit_name ?? 'unit' }})</option>
          </select>
          <input v-model.number="ln.qty" type="number" step="0.0001" class="border rounded px-2 py-2" placeholder="Qty"/>
          <input v-model.number="ln.unit_cost_cents" type="number" class="border rounded px-2 py-2" placeholder="Unit cost (cents)"/>
          <div class="flex gap-2">
            <button class="px-2 py-2 bg-red-600 text-white rounded" @click="removeLine(i)">Remove</button>
          </div>
        </div>
      </div>

      <div class="mt-3 flex gap-2">
        <button class="px-3 py-2 bg-gray-700 text-white rounded" @click="addLine">Add Line</button>
        <button class="px-3 py-2 bg-green-600 text-white rounded" @click="postGRN">Receive Stock</button>
      </div>
    </div>
  </div>
</template>
