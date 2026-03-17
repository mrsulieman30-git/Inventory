<template>
  <Layout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100 sm:text-4xl">
          Pharmacy Command Center
        </h2>
        <div class="flex gap-4">
          <button @click="showModal = true" class="btn btn-primary px-5 py-2">
            New Dispense
          </button>
          <button @click="simulateLoad" class="btn btn-success px-5 py-2">
            Sync eMAR
          </button>
        </div>
      </div>

      <!-- Overview Cards (Glassmorphism) -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="glass-card p-6 border-l-4 border-l-action-info transform hover:scale-105 transition-transform duration-300">
          <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Valuation</p>
          <p class="text-3xl font-bold mt-2 text-slate-800 dark:text-slate-100">$2.4M</p>
          <p class="text-xs text-action-success mt-2 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
            12% vs last month
          </p>
        </div>

        <div class="glass-card p-6 border-l-4 border-l-action-warning transform hover:scale-105 transition-transform duration-300">
          <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Low Stock Alerts</p>
          <p class="text-3xl font-bold mt-2 text-slate-800 dark:text-slate-100">14</p>
          <p class="text-xs text-action-warning mt-2">Requires immediate PO</p>
        </div>

        <div class="glass-card p-6 border-l-4 border-l-action-danger transform hover:scale-105 transition-transform duration-300">
          <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Quarantined (Expiring)</p>
          <p class="text-3xl font-bold mt-2 text-slate-800 dark:text-slate-100">3</p>
          <p class="text-xs text-slate-500 mt-2">Within 30 days of expiry</p>
        </div>

        <div class="glass-card p-6 border-l-4 border-l-primary-500 transform hover:scale-105 transition-transform duration-300">
          <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Pending Prescriptions</p>
          <p class="text-3xl font-bold mt-2 text-slate-800 dark:text-slate-100">89</p>
          <p class="text-xs text-primary-500 mt-2">eMAR synced successfully</p>
        </div>
      </div>

      <!-- 3D Visualization Section -->
      <div class="glass-card mt-8 p-1">
        <div class="p-5 border-b border-slate-200 dark:border-dark-border/50 flex justify-between items-center">
          <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200">Virtual Racks (Live View)</h3>
          <span class="px-3 py-1 bg-action-success/10 text-action-success text-xs font-bold rounded-full">IoT Connected</span>
        </div>
        <!-- Three.js Container -->
        <div ref="threeContainer" class="w-full h-96 bg-gradient-to-b from-slate-100 to-slate-200 dark:from-dark-bg dark:to-slate-900 rounded-b-2xl overflow-hidden relative cursor-move">
          <div class="absolute bottom-4 left-4 text-xs text-slate-500 dark:text-slate-400 pointer-events-none bg-white/50 dark:bg-black/50 p-2 rounded-lg backdrop-blur-sm">
            Interactive: Drag to rotate, scroll to zoom.
          </div>
        </div>
      </div>

    </div>

    <!-- Modals & Overlays -->
    <Modal v-model:show="showModal" title="Confirm Dispensation" actionText="Dispense" actionClass="btn-success" @confirm="handleConfirm">
      <p>Are you sure you want to dispense <strong>2x Amoxicillin 500mg</strong>?</p>
      <div class="mt-4 p-4 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-xl">
        <p class="text-sm text-primary-800 dark:text-primary-300 font-medium">
          <svg class="inline w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
          Clinical Decision Support
        </p>
        <p class="text-xs text-primary-600 dark:text-primary-400 mt-1">
          No known allergies or severe interactions detected for patient PAT-891.
        </p>
      </div>
    </Modal>

    <LoadingOverlay :loading="isLoading" message="Syncing with EHR..." />
  </Layout>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import Layout from '@/Shared/Layout.vue';
import Modal from '@/Components/Modal.vue';
import LoadingOverlay from '@/Components/LoadingOverlay.vue';
import * as THREE from 'three';

const showModal = ref(false);
const isLoading = ref(false);
const threeContainer = ref(null);

// 3D Scene Variables
let scene, camera, renderer, animationId;

const simulateLoad = () => {
  isLoading.value = true;
  setTimeout(() => {
    isLoading.value = false;
  }, 2000);
};

const handleConfirm = () => {
  showModal.value = false;
  // Trigger notification or Inertia post here
};

// Initialize stunning 3D Virtual Racks visualization
const initThreeJS = () => {
  if (!threeContainer.value) return;

  const width = threeContainer.value.clientWidth;
  const height = threeContainer.value.clientHeight;

  scene = new THREE.Scene();
  // Optional: Add a very subtle fog for depth
  scene.fog = new THREE.FogExp2(0x0f172a, 0.03);

  camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
  camera.position.set(15, 10, 20);
  camera.lookAt(0, 0, 0);

  renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
  renderer.setSize(width, height);
  renderer.setPixelRatio(window.devicePixelRatio);
  // Soft shadows
  renderer.shadowMap.enabled = true;
  renderer.shadowMap.type = THREE.PCFSoftShadowMap;

  threeContainer.value.appendChild(renderer.domElement);

  // Lighting (Professional studio setup)
  const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
  scene.add(ambientLight);

  const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
  dirLight.position.set(10, 20, 10);
  dirLight.castShadow = true;
  dirLight.shadow.mapSize.width = 1024;
  dirLight.shadow.mapSize.height = 1024;
  scene.add(dirLight);

  // Materials
  const rackMaterial = new THREE.MeshStandardMaterial({
    color: 0x334155, // Dark slate
    metalness: 0.3,
    roughness: 0.4
  });

  const healthyBoxMat = new THREE.MeshStandardMaterial({ color: 0x10b981, roughness: 0.2 }); // Success Green
  const warningBoxMat = new THREE.MeshStandardMaterial({ color: 0xf59e0b, roughness: 0.2 }); // Warning Orange
  const dangerBoxMat = new THREE.MeshStandardMaterial({ color: 0xef4444, roughness: 0.2 }); // Danger Red (Quarantined)

  // Build the Virtual Rack (Shelves)
  const buildRack = (xOffset) => {
    const group = new THREE.Group();

    // Vertical supports
    const supportGeo = new THREE.BoxGeometry(0.5, 10, 0.5);
    const s1 = new THREE.Mesh(supportGeo, rackMaterial); s1.position.set(-2, 5, -1); s1.castShadow = true;
    const s2 = new THREE.Mesh(supportGeo, rackMaterial); s2.position.set(2, 5, -1); s2.castShadow = true;
    const s3 = new THREE.Mesh(supportGeo, rackMaterial); s3.position.set(-2, 5, 1); s3.castShadow = true;
    const s4 = new THREE.Mesh(supportGeo, rackMaterial); s4.position.set(2, 5, 1); s4.castShadow = true;
    group.add(s1, s2, s3, s4);

    // Shelves
    const shelfGeo = new THREE.BoxGeometry(4.5, 0.2, 2.5);
    for(let i=0; i<4; i++) {
      const shelf = new THREE.Mesh(shelfGeo, rackMaterial);
      shelf.position.set(0, 2 + (i * 2.5), 0);
      shelf.receiveShadow = true;
      group.add(shelf);

      // Populate boxes (Batches)
      populateShelf(group, 2 + (i * 2.5) + 0.6);
    }

    group.position.x = xOffset;
    return group;
  };

  const populateShelf = (group, yPos) => {
    const boxGeo = new THREE.BoxGeometry(0.8, 1, 0.8);
    for(let x=-1.5; x<=1.5; x+=1.2) {
      // Randomly assign health status to boxes
      const rand = Math.random();
      let mat = healthyBoxMat;
      if (rand > 0.8) mat = warningBoxMat;
      if (rand > 0.95) mat = dangerBoxMat;

      // Randomly skip some to show missing stock
      if (Math.random() > 0.2) {
        const box = new THREE.Mesh(boxGeo, mat);
        box.position.set(x, yPos, 0);
        box.castShadow = true;
        group.add(box);
      }
    }
  };

  // Add multiple racks
  scene.add(buildRack(-6));
  scene.add(buildRack(0));
  scene.add(buildRack(6));

  // Floor
  const floorGeo = new THREE.PlaneGeometry(50, 50);
  const floorMat = new THREE.MeshStandardMaterial({ color: 0x94a3b8, roughness: 0.8 });
  const floor = new THREE.Mesh(floorGeo, floorMat);
  floor.rotation.x = -Math.PI / 2;
  floor.receiveShadow = true;
  scene.add(floor);

  // Simple animation loop (Slow rotation to show off 3D)
  let angle = 0;
  const animate = () => {
    animationId = requestAnimationFrame(animate);

    // Auto-rotate camera slowly for cinematic effect
    angle += 0.002;
    camera.position.x = 20 * Math.cos(angle);
    camera.position.z = 20 * Math.sin(angle);
    camera.lookAt(0, 5, 0);

    renderer.render(scene, camera);
  };

  animate();

  // Handle Resize
  window.addEventListener('resize', onWindowResize);
};

const onWindowResize = () => {
  if (!threeContainer.value || !camera || !renderer) return;
  const width = threeContainer.value.clientWidth;
  const height = threeContainer.value.clientHeight;
  camera.aspect = width / height;
  camera.updateProjectionMatrix();
  renderer.setSize(width, height);
};

onMounted(() => {
  initThreeJS();
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', onWindowResize);
  if (animationId) cancelAnimationFrame(animationId);
  // Clean up WebGL context
  if (renderer) renderer.dispose();
});
</script>
