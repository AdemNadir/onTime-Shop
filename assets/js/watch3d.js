/**
 * Ontime - 3D watch hero (Three.js, ES modules, loaded straight from CDN)
 * Freely rotatable with the mouse / touch (OrbitControls), auto-rotates when idle.
 * Procedurally built (no external 3D model file needed) so you can restyle it
 * just by tweaking the colors/materials below. The flat "watch face" texture
 * on the dial is loaded from IMAGE_PATH - change that constant to swap the face image.
 */
import * as THREE from 'https://cdn.jsdelivr.net/npm/[email protected]/build/three.module.js';
import { OrbitControls } from 'https://cdn.jsdelivr.net/npm/[email protected]/examples/jsm/controls/OrbitControls.js';

const IMAGE_PATH = 'assets/img/watch-face.jpg'; // optional: put a dial photo here to use it as the face texture

const mount = document.getElementById('watch3d');
if (mount) {
    const width = mount.clientWidth;
    const height = mount.clientHeight;

    const scene = new THREE.Scene();
    scene.background = null;

    const camera = new THREE.PerspectiveCamera(35, width / height, 0.1, 100);
    camera.position.set(0, 0.6, 5.2);

    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    mount.appendChild(renderer.domElement);

    // --- Lights ---
    scene.add(new THREE.AmbientLight(0xffffff, 0.55));
    const key = new THREE.DirectionalLight(0xffffff, 1.1);
    key.position.set(4, 6, 5);
    scene.add(key);
    const rim = new THREE.PointLight(0xc9a876, 1.2, 20);
    rim.position.set(-4, 2, -3);
    scene.add(rim);
    const fill = new THREE.PointLight(0x4a6fa5, 0.6, 20);
    fill.position.set(3, -2, -2);
    scene.add(fill);

    // --- Watch group ---
    const watch = new THREE.Group();

    // Case (outer ring - gold/brass tone to pop against navy/white palette)
    const caseMat = new THREE.MeshStandardMaterial({ color: 0xc9a876, metalness: 0.9, roughness: 0.25 });
    const caseGeo = new THREE.CylinderGeometry(1.35, 1.35, 0.38, 64);
    const caseMesh = new THREE.Mesh(caseGeo, caseMat);
    caseMesh.rotation.x = Math.PI / 2;
    watch.add(caseMesh);

    // Bezel inner ring (navy)
    const bezelMat = new THREE.MeshStandardMaterial({ color: 0x0b1f3a, metalness: 0.6, roughness: 0.35 });
    const bezelGeo = new THREE.TorusGeometry(1.18, 0.09, 24, 64);
    const bezel = new THREE.Mesh(bezelGeo, bezelMat);
    watch.add(bezel);

    // Dial (face)
    let dialMat;
    const texLoader = new THREE.TextureLoader();
    texLoader.load(
        IMAGE_PATH,
        (tex) => {
            dialMat.map = tex;
            dialMat.needsUpdate = true;
        },
        undefined,
        () => { /* no face image provided yet - plain navy dial is used, that's fine */ }
    );
    dialMat = new THREE.MeshStandardMaterial({ color: 0x0e2a4d, metalness: 0.3, roughness: 0.5 });
    const dialGeo = new THREE.CircleGeometry(1.08, 64);
    const dial = new THREE.Mesh(dialGeo, dialMat);
    dial.position.z = 0.2;
    watch.add(dial);

    // Hour markers
    const markerMat = new THREE.MeshStandardMaterial({ color: 0xf5f1e8, metalness: 0.4, roughness: 0.4 });
    for (let i = 0; i < 12; i++) {
        const angle = (i / 12) * Math.PI * 2;
        const marker = new THREE.Mesh(new THREE.BoxGeometry(0.045, 0.16, 0.03), markerMat);
        marker.position.set(Math.sin(angle) * 0.92, Math.cos(angle) * 0.92, 0.21);
        marker.rotation.z = -angle;
        watch.add(marker);
    }

    // Hands


    


    const handMat = new THREE.MeshStandardMaterial({ color: 0xf5f1e8, metalness: 0.5, roughness: 0.3 });
    const hourHand = new THREE.Mesh(new THREE.BoxGeometry(0.05, 0.5, 0.04), handMat);
    hourHand.position.set(0, 0.22, 0.24);
    hourHand.rotation.z = -0.6;
    watch.add(hourHand);

    const minuteHand = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.75, 0.04), handMat);
    minuteHand.position.set(0, 0.32, 0.26);
    minuteHand.rotation.z = 1.4;
    watch.add(minuteHand);

    const secondMat = new THREE.MeshStandardMaterial({ color: 0xc9a876, metalness: 0.6, roughness: 0.3 });
    const secondHand = new THREE.Mesh(new THREE.BoxGeometry(0.02, 0.85, 0.04), secondMat);
    secondHand.position.set(0, 0.35, 0.27);
    watch.add(secondHand);

    const centerCap = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.06, 0.1, 24), secondMat);
    centerCap.rotation.x = Math.PI / 2;
    centerCap.position.z = 0.27;
    watch.add(centerCap);

    const crown = new THREE.Mesh(new THREE.CylinderGeometry(0.09, 0.09, 0.16, 16), caseMat);
    crown.rotation.z = Math.PI / 2;
    crown.position.set(1.42, 0, 0);
    watch.add(crown);

    // Strap (two simple curved segments, one on each side)
    const strapMat = new THREE.MeshStandardMaterial({ color: 0x111417, roughness: 0.9, metalness: 0.05 });
    function makeStrapSegment(yOffset) {
        const curve = new THREE.CatmullRomCurve3([
            new THREE.Vector3(0, yOffset, 0),
            new THREE.Vector3(0, yOffset * 2.4, -0.05),
            new THREE.Vector3(0, yOffset * 4.2, -0.15),
        ]);
        const geo = new THREE.TubeGeometry(curve, 20, 0.55, 8, false);
        return new THREE.Mesh(geo, strapMat);
    }
    const strapTop = makeStrapSegment(1.35);
    const strapBottom = makeStrapSegment(-1.35);
    watch.add(strapTop, strapBottom);

    watch.rotation.x = 0.15;
    scene.add(watch);

    // --- Controls (free rotation by drag) ---
    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.08;
    controls.enableZoom = true;
    controls.minDistance = 3;
    controls.maxDistance = 8;
    controls.enablePan = false;
    controls.autoRotate = true;
    controls.autoRotateSpeed = 1.4;

    // Pause auto-rotate while the user is actively dragging, resume after
    controls.addEventListener('start', () => (controls.autoRotate = false));
    let resumeTimer;
    controls.addEventListener('end', () => {
        clearTimeout(resumeTimer);
        resumeTimer = setTimeout(() => (controls.autoRotate = true), 3000);
    });

    function animate() {
        requestAnimationFrame(animate);
        secondHand.rotation.z = -(Date.now() / 1000 % 60) / 60 * Math.PI * 2;
        controls.update();
        renderer.render(scene, camera);
    }
    animate();

    window.addEventListener('resize', () => {
        const w = mount.clientWidth, h = mount.clientHeight;
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        renderer.setSize(w, h);
    });
}
