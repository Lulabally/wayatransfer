<template>
    <div class="main">
        <div class="form-transfer">
            <form name="transfer" method="post" id="transfer-form" novalidate="true" enctype="multipart/form-data" v-on:submit.prevent="onSubmit">
                <div class="form-transfer-group">
                    <!-- FILE UPLOAD -->
                    <div class="form-transfer-top">
                        <div class="inner-form-group">
                            <input 
                                type="file" 
                                id="transfer_file" 
                                class="file-input"
                                name="transfer[file]" 
                                ref="file" 
                                required="required" 
                                v-on:change="checkFile()">
                            
                            <label for="transfer_file" class="file-label">
                                <div v-bind:class="'progress-circle p'+loadPercentage">
                                    <span class="upload-icon" :class="{ uploaded: isUploaded }">+</span>
                                    <div class="left-half-clipper">
                                        <div class="first50-bar"></div>
                                        <div class="value-bar"></div>
                                    </div>
                                </div>
                                <div class="label-text">
                                    <div class="label-title">
                                        <span v-if="file.name">{{ file.name }}</span>
                                        <span v-else>Ajouter votre fichier</span>
                                    </div>
                                    <div class="label-description">
                                        <div v-if="file && !errors.file.length">
                                            {{ file.type }}<br>
                                            {{ fileSize }}<br><br>
                                        </div>
                                        <div v-else>
                                            <span :class="{ textHidden: errors.file.length }">
                                                Texte, image, document, audio, vidéo, 
                                                <br>feuille de calculs, présentation, archive
                                                <br>Taille maximale : 10 Mo
                                            </span>
                                            <span class="input-error-message" v-if="errors.file.length">
                                                {{ errors.file }}
                                            </span>
                                        </div>
                                        
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div><!-- form-transfer-top -->
                
                    <div class="form-transfer-bottom">
                        <div class="inner-form-group">
                            <!-- EMAIL INPUT -->
                            <div class="text-input-group" v-if="!isUploaded">
                                <div class="text-input">
                                    <input 
                                        id="transfer_recipient" 
                                        class="text-input-field" 
                                        type="email"
                                        name="transfer[recipient]" 
                                        required="required" 
                                        v-model="recipient" 
                                        v-on:change="checkEmail()"
                                        placeholder="Envoyer à">
                                    <label for="transfer_recipient" class="text-input-label">
                                        <span class="label-text">Envoyer à</span>
                                    </label>
                                </div>
                                <span class="input-error-message" v-if="errors.email.length">{{ errors.email }}</span>
                            </div>
                            <div class="text-input-group" v-else>
                                Votre fichier a bien envoyé à {{ recipient }} !
                            </div>
                            
                            <!-- SUBMIT -->
                            <button 
                                type="submit" 
                                form="transfer-form" 
                                value="Transférer" 
                                v-if="!isUploaded"
                                :disabled="!activeSubmitButton" 
                                :class="{ uploading: isUploading }">
                                <span class="spinner"></span>
                                Transférer
                            </button>

                            <button v-else v-on:click.prevent="resetForm()">Un autre ?</button>
                        </div>
                    </div>
                </div>
            </form> 
        </div>
    </div> 
</template>

<script>
import axios from "axios";

const API_ENDPOINT = '/api/v1/transfers'
const MAX_UPLOAD = 1024 * 1024 *10 // 1MO
/* Based on allowed extensions : https://mailchimp.com/fr/help/share-files-with-contacts/ */
const ALLOWED_EXTENSIONS = [
    'txt', 
    'jpg', 'jpe', 'jpeg', 'gif', 'png', 'bmp', 'psd', 'tif', 'tiff', 'svg', 'indd', 'ai', 'eps',
    'doc', 'docx', 'rtf', 'pdf', 
    'mp3', 'm4a', 'm4v', 'wma', 'ogg', 'flac', 'wav', 'aif', 'aifc', 'aiff',
    'mp4', 'mov', 'avi', 'mkv', 'mpeg', 'mpg', 'wmv',
    'xls', 'xlsx',
    'pptx',
    'zip'
]

export default {
    data() {
        return {
            loadPercentage: 0,
            errors: {
                email: '',
                file: ''
            },
            file: '',
            recipient: '',
            isUploading: false,
            isUploaded: false
        }
    },
    computed: {
        activeSubmitButton() {
            return (this.file != '' && this.recipient != '' && this.errors.email === '' && this.errors.file === '');
        },
        fileSize() {
            let k = 1024,
                sizes = ['Bytes', 'Ko', 'Mo', 'Go', 'Tb', 'PB', 'EB', 'ZB', 'YB'],
                i = Math.floor(Math.log(this.file.size) / Math.log(k));
            return parseFloat((this.file.size / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    },
    methods: {
        onSubmit (e) {
            if (!this.errors.length) {
                this.isUploading = true;

                let formData = new FormData(e.target)

                const config = { onUploadProgress: progressEvent => { 
                    this.loadPercentage = Math.round( (progressEvent.loaded * 100) / progressEvent.total )
                }}

                axios
                    .post(API_ENDPOINT, formData, config)
                    .then((response) => { 
                        if (response.status === 201) {
                            this.isUploaded = true;
                            this.isUploading = false;
                        }
                    })
            } 
        },
        checkFile (e) {
            this.errors.file = '';
            this.file = this.$refs.file.files[0];

            const fileExtension = this.file.name.split(".").pop();

            if (!this.file) {
                this.errors.file = 'Vous devez ajouter un fichier'
            }

            if(!ALLOWED_EXTENSIONS.includes(fileExtension)){
                this.errors.file = 'Ce type de fichier n\'est pas autorisé'
            }

            if (this.file.size > MAX_UPLOAD /* 10*/) {
                this.errors.file = 'La taille de votre fichier ne doit pas dépasser 10Mo'
            }
        },
        checkEmail (e) {
            this.errors.email = '';
            if (!this.recipient) {
                this.errors.email = 'Ce champs est requis'
            } else if (!this.validEmail(this.recipient)) {
                this.errors.email = 'Vous devez renseigner une adresse email valide'
            }
        },
        validEmail (email) {
            var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        },
        resetForm () {
            this.file = ''
            this.recipient = ''
            this.isUploaded = false
            this.isUploading = false
        }
    }
};
</script>